<?php

namespace PayuBundle\Entity;

interface OrderInterface
{
    public function getId();
    public function getName();
    public function getTotalPrice();
    public function getDescription();
}