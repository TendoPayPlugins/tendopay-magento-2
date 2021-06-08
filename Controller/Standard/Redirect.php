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

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use TendoPay\SDK\Exception\TendoPayConnectionException;
use TendoPay\TendopayPayment\Controller\TendopayAbstract;
use TendoPay\TendopayPayment\Helper\Data;

/**
 * Class Redirect
 * @package TendoPay\TendopayPayment\Controller\Standard
 */
class Redirect extends TendopayAbstract
{
    public function execute()
    {
        try {
            $url = $this->getRedirectUrl();
            if ($url) {
                $this->getResponse()->setRedirect($url);
                return;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addWarningMessage($e->getMessage());
        } catch (TendoPayConnectionException $e) {
            $this->messageManager->addWarningMessage($e->getMessage());
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Call API to get Authorization Url
     *
     * @param Quote $quote
     * @return string
     * @throws LocalizedException
     * @throws TendoPayConnectionException
     */
    public function requestUrl(Quote $quote)
    {
        $quote = $quote->collectTotals();

        if ($quote->getGrandTotal() < 100) {
            throw new LocalizedException(
                __('The minimum purchase amount is ₱100. Please increase your cart total.')
            );
        }

        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);
        $client = $this->_clientFactory->create();

        $payment = $this->_paymentFactory->create();
        $payment->setMerchantOrderId($quote->getReservedOrderId())
            ->setRequestAmount((int)$quote->getGrandTotal())
            ->setCurrency($quote->getQuoteCurrencyCode())
            ->setRedirectUrl($this->checkoutHelper->getRedirectUrl());
        /* Debug */
        if ($this->_config->getValue('debug')) {
            $requestData = [
                Data::PAYMENT_REQUST_PARAM_AMOUNT => $payment->getRequestAmount(),
                Data::PAYMENT_REQUST_PARAM_MECHANT_ORDER_ID => $payment->getMerchantOrderId(),
                Data::PAYMENT_REQUST_PARAM_CURRENCY => $payment->getCurrency(),
                Data::PAYMENT_REQUST_PARAM_REDIRECT => $payment->getRedirectUrl(),
                Data::PAYMENT_REQUST_PARAM_DESCRIPTION => $payment->getDescription()
            ];
            $this->logger->debug(json_encode($requestData));
        }
        /* END Debug */

        $client->setPayment($payment);
        return $client->getAuthorizeLink();
    }

    /**
     * @throws LocalizedException
     * @throws TendoPayConnectionException
     */
    public function getRedirectUrl()
    {
        $this->initCheckout();
        $quote = $this->getQuote();

        return $this->requestUrl($quote);
    }
}
