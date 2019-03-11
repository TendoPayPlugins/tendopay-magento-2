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

namespace TendoPay\TendopayPayment\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ApiErrorsObserver
 * @package TendoPay\TendopayPayment\Observer
 */
class ApiErrorsObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $salesOrder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \TendoPay\TendopayPayment\Helper\Data
     */
    protected $tendopayHelper;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * ApiErrorsObserver constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order $salesOrder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \TendoPay\TendopayPayment\Helper\Data $tendopayHelper
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Framework\App\RequestInterface $request,
        \TendoPay\TendopayPayment\Helper\Data $tendopayHelper,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->salesOrder = $salesOrder;
        $this->request = $request;
        $this->tendopayHelper = $tendopayHelper;
        $this->quote = $quote;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
    }

    /**
     * @param EventObserver $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getModuleName() == 'checkout' && $this->request->getControllerName() == 'cart' &&
            $this->request->getActionName() == 'index') {
            $orderId = $this->checkoutSession->getLastRealOrderId();
            $order = $this->salesOrder->loadByIncrementId($orderId);
            $this->maybeAddPaymentFailedNotice($order);
            $this->maybeAddOutstandingBalanceNotice($order);
        }
    }

    /**
     * @param $order
     * @throws \Exception
     */
    private function maybeAddPaymentFailedNotice($order)
    {
        $paymentFailed = $this->request->getParam($this->tendopayHelper->paymentFailedQueryParam());
        if ($paymentFailed) {
            $paymentFailedNotice = 'The payment attempt with TendoPay has failed.
             Please try again or choose other payment method.';
            $this->tendopayHelper->addTendopayError($paymentFailedNotice);
            if ($order->getId()) {
                $order->cancel()->save();
                $quote = $this->quote->load($order->getQuoteId());
                if ($quote->getId()) {
                    $quote->setIsActive(1)->setReservedOrderId(null)->save();
                    $this->tendopayHelper->getCheckoutSession()->replaceQuote($quote);
                }
            }
        }
    }

    /**
     * @param $order
     * @return $this
     * @throws \Exception
     */
    private function maybeAddOutstandingBalanceNotice($order)
    {
        $witherror = $this->request->getParam('witherror');
        if ($witherror) {
            $errors = explode(':', $witherror);
            $errors = is_array($errors) ? array_map('htmlspecialchars', $errors) : [];
            $error = isset($errors[0]) ? $errors[0] : '';
            $extra = isset($errors[1]) ? $errors[1] : '';

            if ($order->getId()) {
                $order->cancel()->save();
                $quote = $this->quote->load($order->getQuoteId());
                if ($quote->getId()) {
                    $quote->setIsActive(1)->setReservedOrderId(null)->save();
                    $this->tendopayHelper->restoreQuote();
                }
            }

            switch ($error) {
                case 'outstanding_balance':
                    $notice = "Your account has an outstanding balance, 
                    please repay your payment so you make an additional purchase.";
                    $this->tendopayHelper->addTendopayError($notice);
                    break;
                case 'minimum_purchase':
                case 'maximum_purchase':
                    $notice = __($extra);
                    $this->tendopayHelper->addTendopayError($notice);
            }
            $redirectionUrl = $this->url->getUrl('checkout/cart');
            return $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
        }
    }
}
