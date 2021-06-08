<?php

namespace TendoPay\TendopayPayment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\Method\Logger;
use TendoPay\TendopayPayment\Helper\Data;

class CompleteSaleHandler implements HandlerInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * CompleteAuthHandler constructor.
     *
     * @param Logger $logger
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Logger $logger,
        SubjectReader $subjectReader
    ) {
        $this->logger = $logger;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws \Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        /**
         * $payment \Magento\Sales\Model\Order\Payment
         */

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        if ($response[Data::TRANSACTION_INFO_STATUS]) {
            $payment->setTransactionId($response[Data::TRANSACTION_INFO_ID]);
            $payment->setAdditionalInformation(Data::TRANSACTION_INFO_STATUS, $response[Data::TRANSACTION_INFO_STATUS]);
            $payment->setAdditionalInformation(Data::TRANSACTION_INFO_CREATED_AT, $response[Data::TRANSACTION_INFO_CREATED_AT]);
            $payment->setIsTransactionClosed(true);

            if ($response[Data::TRANSACTION_INFO_STATUS] === Data::TRANSACTION_STATUS_PAID) {
                $payment->setIsTransactionApproved(true);
            } elseif ($response[Data::TRANSACTION_INFO_STATUS] === Data::TRANSACTION_STATUS_CANCELED) {
                $payment->setIsTransactionDenied(true);
                $order = $payment->getOrder();
                $order->setState($order::STATE_CANCELED)->setStatus($order::STATE_CANCELED);
            }
        }
    }
}
