<?php
/**
 * TendoPay
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customize this module for your needs.
 *
 * @category   TendoPay
 * @package    TendoPay_TendopayPayment
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TendoPay\TendopayPayment\Controller\Standard;

use \TendoPay\TendopayPayment\Model\Standard;
use \TendoPay\TendopayPayment\Helper\Data as tendoPayHelper;

/**
 * Class Success
 * @package TendoPay\TendopayPayment\Controller\Standard
 */
class Success extends \TendoPay\TendopayPayment\Controller\TendopayAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $postedData = $this->getRequest()->getParams();
        if (isset($postedData['action'])) {
            unset($postedData['action']);
        }

        $getTendopayCustomerReferenceOne = $this->checkoutHelper->getTendopayCustomerReferenceOne();
        $order = $this->orderFactory->create()->loadByIncrementId($postedData[$getTendopayCustomerReferenceOne]);

        if ($order->getIncrementId()) {
            $orderKey = $postedData[$getTendopayCustomerReferenceOne];
            if ($order->getIncrementId() !== $orderKey) {
                $this->checkoutHelper->addTendopayError("Wrong order key provided");
            } else {
                switch ($order->getStatus()) {
                    case tendoPayHelper::RESPONSE_STATUS_APPROVED:
                        $this->preVerifyPaymentAction($order, $postedData);
                        break;
                    case tendoPayHelper::RESPONSE_STATUS_PROCESSING:
                    case tendoPayHelper::RESPONSE_STATUS_PENDING:
                        $this->preVerifyPaymentAction($order, $postedData);
                        break;
                    case tendoPayHelper::RESPONSE_STATUS_DECLINED:
                        $this->cancelAction();
                        $this->paymentMethod->resetTransactionToken();
                        $this->checkoutHelper->addTendopayError(
                            'TendoPay payment has been declined. Please use other payment method.'
                        );
                        break;
                    default:
                        $this->cancelAction();
                        $this->paymentMethod->resetTransactionToken();
                        $this->checkoutHelper->addTendopayError(
                            'Cannot find TendoPay payment. Please contact administrator.'
                        );
                        break;
                }
                $this->tendopaySuccessAction($order);
            }
        }
        $this->_redirect('checkout/onepage/success', ['_secure' => true]);
    }

    /**
     * @throws \Exception
     */
    public function tendopaySuccessAction($order)
    {
        $this->checkoutSession->clearHelperData();
        $quoteId = $this->checkoutSession->getQuote()->getId();
        $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);
        if ($order) {
            $this->checkoutSession->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());
        }
        $this->_redirect('checkout/onepage/success', ['_secure' => true]);
    }

    /**
     * @param $order
     * @param $postedData
     */
    public function preVerifyPaymentAction($order, $postedData)
    {
        $this->performVerification($order, $postedData);
    }

    /**
     * @param $order
     * @param $postedData
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function performVerification($order, $postedData)
    {
        $tendoPayMerchantId = $postedData[$this->checkoutHelper->getVendorIdParam()];
        $localTendoPayMerchantId = $this->checkoutHelper->getConfigValues(
            $this->checkoutHelper->getAPIMerchantIDConfigField()
        );

        if ($tendoPayMerchantId !== $localTendoPayMerchantId) {
            $this->checkoutHelper->addTendopayError("Malformed payload");
        }

        try {
            $transactionVerified = $this->verifyPayment($order, $postedData);
        } catch (\Exception $exception) {
            $this->checkoutHelper->addTendopayError("Could not communicate with TendoPay properly");
        }

        if ($transactionVerified) {
            return true;
        } else {
            $order->sendNewOrderEmail()->addStatusHistoryComment(
                'Could not get with TendoPay transaction verification properly'
            )
                ->setIsCustomerNotified(false)
                ->save();
            $this->_forward('error');
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Call API to verify Payment
     *
     * @param $order
     * @param array $data
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function verifyPayment($order, array $data)
    {
        ksort($data);
        $hash = $data[$this->checkoutHelper->getHashParam()];
        if ($hash !== $this->checkoutHelper->calculate($data)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Hash doesn't match", "tendopay")
            );
        }
        $disposition = $data[$this->checkoutHelper->getDispositionParam()];
        $tendoPayTransactionNumber = $data[$this->checkoutHelper->getTransactionNoParam()];
        $verificationToken = $data[$this->checkoutHelper->getVerificationTokenParam()];
        $tendoPayUserId = $data[$this->checkoutHelper->getUserIDParam()];

        $verificationData = [
            $this->checkoutHelper->getTendopayCustomerReferenceOne() => (string)$order->getIncrementId(),
            $this->checkoutHelper->getTendopayCustomerReferencetwo() => "magento2_order_" . $order->getIncrementId(),
            $this->checkoutHelper->getDispositionParam() => $disposition,
            $this->checkoutHelper->getVendorIdParam() => (string)$this->checkoutHelper->getConfigValues(
                $this->checkoutHelper->getAPIMerchantIDConfigField()
            ),
            $this->checkoutHelper->getTransactionNoParam() => (string)$tendoPayTransactionNumber,
            $this->checkoutHelper->getVerificationTokenParam() => $verificationToken,
            $this->checkoutHelper->getUserIDParam() => $tendoPayUserId,
        ];

        $response = $this->checkoutHelper->doCall(
            $this->checkoutHelper->getVerificationEndpointUri(),
            $verificationData
        );

        if ($response->getCode() !== 200) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Received error: %1 while trying to verify the transaction', $response->getCode())
            );
        }

        $json = json_decode($response->getBody());

        $session = $this->checkoutHelper->getCheckoutSession();
        $quote = $session->getQuote();
        $quote->setData('tendopay_order_id', $order->getTendopayOrderId())->save();
        $quote->setData('tendopay_token', $order->getTendopayToken())->save();

        $order->setData('tendopay_token', $json->tendopay_hash);
        $order->setData('tendopay_order_id', $json->tendopay_transaction_number);
        $order->setData('tendopay_disposition', $this->getRequest()->getParam('tendopay_disposition'));
        $order->setData('tendopay_verification_token', $this->getRequest()->getParam('tendopay_verification_token'));
        $order->setData('tendopay_fetched_at', $this->checkoutHelper->getGmtDate());
        $order->save();

        return $json->{$this->checkoutHelper->getStatusIDParam()} === 'success';
    }

    /**
     * Cancel Action
     *
     * @throws \Exception
     */
    public function cancelAction()
    {
        $this->checkoutSession->setQuoteId($this->checkoutSession->getTendopayStandardQuoteId(true));
        if ($this->checkoutSession->getLastRealOrderId()) {
            $order = $this->orderFactory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
            $this->checkoutHelper->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }
}
