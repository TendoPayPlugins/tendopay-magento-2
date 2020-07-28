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

/**
 * Class Redirect
 * @package TendoPay\TendopayPayment\Controller\Standard
 */
class Redirect extends \TendoPay\TendopayPayment\Controller\TendopayAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $this->checkoutHelper->addTendopayLog(
            'Initiate Redirect Method',
            'info'
        );
        if (!$this->getRequest()->isAjax()) {
            $this->_cancelPayment();
            $this->checkoutSession->restoreQuote();
            $this->getResponse()->setRedirect(
                $this->getCheckoutHelper()->getUrl('checkout')
            );
        }

        $quote = $this->getQuote();
        $email = $this->getRequest()->getParam('email');
        if ($this->getCustomerSession()->isLoggedIn()) {
            $this->getCheckoutSession()->loadCustomerQuote();
            $quote->updateCustomerData($this->getQuote()->getCustomer());
        } else {
            $quote->setCustomerEmail($email);
        }

        if ($this->getCustomerSession()->isLoggedIn()) {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
        } else {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
        }

        $quote->setCustomerEmail($email);
        $quote->save();

        $order = $this->getOrder();
        if (!$order->getIncrementId()) {
            $message = 'Payment redirect request: Cannot get order from session, redirecting customer to shopping cart';
            $this->messageManager->addErrorMessage(
                $message
            );
            $this->checkoutHelper->addTendopayLog($message, 'error');
            return $this->_redirect('checkout/cart');
        }
        $this->checkoutHelper->addTendopayLog(
            'Payment redirect request for order ' . $order->getIncrementId(),
            'info'
        );
        $this->checkoutHelper->addTendopayLog(
            'Redirecting customer to TendoPay website... order=' . $order->getIncrementId(),
            'info'
        );
        try {
            $authToken = $this->requestToken($order);
            $this->setDescription($authToken, $order);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Could not communicate with TendoPay."));
        }

        $session = $this->checkoutHelper->getCheckoutSession();
        $session->setTendopayStandardQuoteId($session->getQuoteId());
        $params = $this->getPaymentMethod()->buildCheckoutRequest($authToken);

        if (empty($params)) {
            $this->checkoutHelper->addTendopayLog(
                'Exception on processing payment redirect request',
                'error'
            );
            $this->cancelAction();
        }
        return $this->resultJsonFactory->create()->setData($params);
    }

    /**
     * Call API to get Authorization Token
     *
     * @param $order
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToken($order)
    {
        $data = [
            $this->checkoutHelper->getAmountParam() => (int)($order->getGrandTotal()),
            $this->checkoutHelper->getTendopayCustomerReferenceOne() => (string)$order->getIncrementId(),
            $this->checkoutHelper->getTendopayCustomerReferencetwo() => "magento2_order_" . $order->getIncrementId(),
        ];

        $response = $this->checkoutHelper->doCall($this->checkoutHelper->getAuthorizationEndpointUri(), $data);
        $isValidResponse = $response->getCode() === 200 && !empty($response->getBody());

        if (!$isValidResponse) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Got return code != 200 or empty body while requesting authorization token from TP')
            );
        }

        return trim((string)$response->getBody(), "\"");
    }

    /**
     * Call API to send order details to tendopay
     *
     * @param $authorizationToken
     * @param $order
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setDescription($authorizationToken, $order)
    {
        $orderDetails = null;
        try {
            $orderDetails = $this->checkoutHelper->getApiAdapter()->buildOrderTokenRequest($order);
            if (!is_array($orderDetails) && !is_object($orderDetails)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Order details parameter must be either ARRAY or OBJECT')
                );
            }
        } catch (\Exception $e) {
            $this->checkoutHelper->addTendopayLog($e->getMessage(), 'warning');
            $orderDetails = new \StdClass;
        }

        $response = $this->checkoutHelper->doCall(
            $this->checkoutHelper->getDescriptionEndpointUri(),
            [
                $this->checkoutHelper->getAuthTokenParam() => $authorizationToken,
                $this->checkoutHelper->getTendopayCustomerReferenceOne() => (string)$order->getIncrementId(),
                $this->checkoutHelper->getTendopayCustomerReferencetwo() => "magento2_order_" .
                    $order->getIncrementId(),
                $this->checkoutHelper->getDescParam() => json_encode($orderDetails),
            ]
        );

        if ($response->getCode() !== 204) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Got response code != 204 while sending products description')
            );
        }
    }

    /**
     * Cancel Action
     *
     * @throws \Exception
     */
    private function cancelAction()
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
