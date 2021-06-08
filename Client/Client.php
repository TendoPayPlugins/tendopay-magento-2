<?php


namespace TendoPay\TendopayPayment\Client;


use TendoPay\SDK\V2\TendoPayClient;

class Client extends TendoPayClient
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }
}
