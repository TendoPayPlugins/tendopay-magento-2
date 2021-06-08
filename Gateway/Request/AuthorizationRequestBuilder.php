<?php

namespace TendoPay\TendopayPayment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TendoPay\TendopayPayment\Helper\Data;

class AuthorizationRequestBuilder implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;


    /**
     * AuthorizationRequestBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param ConfigInterface $config
     */
    public function __construct(
        SubjectReader $subjectReader,
        ConfigInterface $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $data = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $data[Data::PAYMENT_INFO_TP_TRANSACTION_ID] = $payment->getAdditionalInformation(Data::PAYMENT_INFO_TP_TRANSACTION_ID);

        return $data;
    }
}
