<?php

namespace TendoPay\TendopayPayment\Gateway\Http\Client;

use TendoPay\TendopayPayment\Helper\Data;

/**
 * Class Client
 * Tendo Pay gateway capture client
 */
class CaptureClient extends AbstractClient
{

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function process(array $data)
    {
        $client = $this->_clientFactory->create();
        $response = $client->getTransactionDetail($data[Data::PAYMENT_INFO_TP_TRANSACTION_ID]);
        $response = $response->toArray();
        return $response;
    }
}
