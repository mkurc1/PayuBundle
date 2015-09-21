<?php

namespace PayuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class PayuOrderRequest
{
    const STATUS_NEW = 1;
    const STATUS_PENDING = 2;
    const STATUS_WAITING_FOR_CONFIRMATION = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_CANCELED = 5;
    const STATUS_REJECTED = 6;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15)
     */
    protected $customerIp;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $status = self::STATUS_NEW;


    abstract public function getOrder();
    abstract public function setOrder($order);

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCustomerIp()
    {
        return $this->customerIp;
    }

    /**
     * @param mixed $customerIp
     */
    public function setCustomerIp($customerIp)
    {
        $this->customerIp = $customerIp;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}