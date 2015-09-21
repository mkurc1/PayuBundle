<?php

namespace PayuBundle\Controller;

use PayuBundle\Entity\PayuOrderRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/payu")
 */
class PayuController extends Controller
{
    /**
     * @Route("/notify")
     * @Method("POST")
     */
    public function notificationAction(Request $request)
    {
        $this->get('payu.client')->checkRequest($request);

        return new Response(Response::HTTP_OK);
    }

    /**
     * @Route("/error/{sessionId}")
     * @Method("GET")
     */
    public function errorAction($sessionId)
    {
        $orderRequest = $this->get('payu.client')->getOrderRequest($sessionId);

        $this->get('monolog.logger.payu')->error('PayU transaction was canceled! Order ID ' . $orderRequest->getId());
        if ($orderRequest->getStatus() == PayuOrderRequest::STATUS_NEW) {
            $orderRequest->setStatus(PayuOrderRequest::STATUS_CANCELED);
            $this->getDoctrine()->getManager()->flush();
        }

        $this->get('session')->getFlashBag()->add("error", $this->get('translator')->trans('error.flash', [], 'PayuBundle'));
        return $this->redirectToRoute($this->container->getParameter('payu.redirect'));
    }

    /**
     * @Route("/success/{sessionId}")
     * @Method("GET")
     */
    public function successAction(Request $request, $sessionId)
    {
        $orderRequest = $this->get('payu.client')->getOrderRequest($sessionId);

        if ($request->query->get('error') == Response::HTTP_NOT_IMPLEMENTED) {
            return $this->redirectToRoute('payu_payu_error', compact('sessionId'));
        }

        $this->get('monolog.logger.payu')->info('PayU transaction was pending! Order ID ' . $orderRequest->getId());
        if ($orderRequest->getStatus() == PayuOrderRequest::STATUS_NEW) {
            $orderRequest->setStatus(PayuOrderRequest::STATUS_PENDING);
            $this->getDoctrine()->getManager()->flush();
        }

        $this->get('session')->getFlashBag()->add("success", $this->get('translator')->trans('success.flash', [], 'PayuBundle'));
        return $this->redirectToRoute($this->container->getParameter('payu.redirect'));
    }
}