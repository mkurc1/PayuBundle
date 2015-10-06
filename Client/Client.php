<?php

namespace PayuBundle\Client;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use PayuBundle\Exception\NotPriceDefinedException;
use OpenPayU_Configuration;
use OpenPayU_Order;
use PayuBundle\Entity\OrderInterface;
use PayuBundle\Entity\PayuOrderRequest;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class Client
{
    const CURRENCY_CODE = 'PLN';

    /** @var EntityManagerInterface */
    private $em;
    /** @var Request */
    private $request;
    /** @var Router */
    private $router;
    /** @var LoggerInterface */
    private $logger;

    private $orderRequestClass;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, Router $router, LoggerInterface $logger, $postId, $signatureKey, $environment, $orderRequestClass, $cipher = 'TLSv1')
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->logger = $logger;

        OpenPayU_Configuration::setEnvironment($environment);
        OpenPayU_Configuration::setMerchantPosId($postId);
        OpenPayU_Configuration::setSignatureKey($signatureKey);
        OpenPayU_Configuration::setServiceSslCipherList($cipher);
        $this->orderRequestClass = $orderRequestClass;
    }

    private function setDataToRequest(OrderInterface $order)
    {
        $orderRequest = $this->createOrderRequest($order);
        $this->logger->notice('Create request to payu. Order ID ' . $orderRequest->getId());

        return [
            'notifyUrl'     => $this->router->generate('payu_payu_notification', [], true),
            'continueUrl'   => $this->router->generate('payu_payu_success', ['sessionId' => $orderRequest->getId()], true),
            'customerIp'    => $this->request->getClientIp(),
            'merchantPosId' => OpenPayU_Configuration::getMerchantPosId(),
            'description'   => $order->getDescription(),
            'currencyCode'  => self::CURRENCY_CODE,
            'totalAmount'   => $this->preparePrice($order->getTotalPrice()),
            'extOrderId'    => $orderRequest->getId(),
            'products'      => [
                [
                    'name'      => $order->getName(),
                    'unitPrice' => $this->preparePrice($order->getTotalPrice()),
                    'quantity'  => 1
                ]
            ]
        ];
    }

    private function preparePrice($price)
    {
        return (int)($price * 100);
    }

    public function createRequest(OrderInterface $order)
    {
        if (!$order->getTotalPrice()) {
            throw new NotPriceDefinedException;
        }

        $request = $this->setDataToRequest($order);

        return OpenPayU_Order::create($request);
    }

    public function checkRequest(Request $request)
    {
        $result = OpenPayU_Order::consumeNotification($request->getContent());

        if ($result->getResponse()->order->orderId) {
            $order = OpenPayU_Order::retrieve($result->getResponse()->order->orderId);

            $orderRequest = $this->getOrderRequest($result->getResponse()->order->extOrderId);
            if ($orderRequest->getStatus() == PayuOrderRequest::STATUS_COMPLETED) {
                throw new EntityNotFoundException;
            }

            if ($order->getStatus() == OpenPayU_Order::SUCCESS) {
                $orderRequest->setStatus(PayuOrderRequest::STATUS_COMPLETED);
                $this->logger->info('PayU transaction was completed! Order ID ' . $orderRequest->getId());
            } else {
                $orderRequest->setStatus(PayuOrderRequest::STATUS_REJECTED);
                $this->logger->error('PayU transaction was rejected! Order ID ' . $orderRequest->getId());
            }

            $this->em->flush();
        }
    }

    public function getOrderRequest($sessionId)
    {
        /** @var PayuOrderRequest $orderRequest */
        $orderRequest = $this->em->find($this->orderRequestClass, $sessionId);
        if (!$orderRequest) {
            throw new EntityNotFoundException;
        }

        return $orderRequest;
    }

    private function createOrderRequest(OrderInterface $order)
    {
        /** @var PayuOrderRequest $orderRequest */
        $orderRequest = new $this->orderRequestClass();
        $orderRequest->setOrder($order);
        $orderRequest->setId($order->getId() . md5(rand()));
        $orderRequest->setCustomerIp($this->request->getClientIp());
        $this->em->persist($orderRequest);
        $this->em->flush();

        return $orderRequest;
    }
}