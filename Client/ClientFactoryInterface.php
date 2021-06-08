<?php


namespace TendoPay\TendopayPayment\Client;


use Magento\Store\Model\ScopeInterface;

interface ClientFactoryInterface
{
    /**
     * @return Client
     */
    public function create();

}
