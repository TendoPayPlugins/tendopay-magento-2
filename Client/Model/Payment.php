<?php


namespace TendoPay\TendopayPayment\Client\Model;


class Payment extends \TendoPay\SDK\Models\Payment
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
