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

namespace TendoPay\TendopayPayment\Controller;

use Magento\Framework\App\Action\Context;
use TendoPay\TendopayPayment\Client\ClientFactoryInterface;

abstract class TendopayAbstract extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \TendoPay\TendopayPayment\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \TendoPay\TendopayPayment\Model\Service\TendoFactory
     */
    private $_tendoService;
    /**
     * @var \TendoPay\TendopayPayment\Model\Service\Tendo
     */
    protected $_service;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var ClientFactoryInterface
     */
    protected $_clientFactory;
    /**
     * @var \TendoPay\TendopayPayment\Client\Model\PaymentFactory
     */
    protected $_paymentFactory;
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    protected $_config;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Psr\Log\LoggerInterface $logger,
        \TendoPay\TendopayPayment\Helper\Data $checkoutHelper,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Payment\Gateway\ConfigInterface $config,
        \TendoPay\TendopayPayment\Model\Service\TendoFactory $tendoService,
        ClientFactoryInterface $clientFactory,
    \TendoPay\TendopayPayment\Client\Model\PaymentFactory $paymentFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->orderFactory = $orderFactory;
        $this->checkoutHelper = $checkoutHelper;
        $this->cartManagement = $cartManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->_tendoService = $tendoService;
        $this->context = $context;
        $this->_clientFactory = $clientFactory;
        $this->_paymentFactory = $paymentFactory;
        $this->_config = $config;
        $this->_scopeConfig = $scopeConfigInterface;
    }

    public function getStoreName()
    {
        return $this->_scopeConfig->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Instantiate quote and checkout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initCheckout()
    {
        $quote = $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize checkout.'));
        }
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return false|string
     */
    protected function _cancelPayment($errorMsg = '')
    {
        $gotoSection = false;
        $this->checkoutHelper->cancelCurrentOrder($errorMsg);
        if ($this->checkoutSession->restoreQuote()) {
            //Redirect to payment step
            $gotoSection = 'paymentMethod';
        }

        return $gotoSection;
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrderById($order_id)
    {
        $order_info = $this->orderFactory->create()->loadByIncrementId($order_id);
        return $order_info;
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId()
        );
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->getCheckoutSession()->getQuote();
        }
        return $this->quote;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \TendoPay\TendopayPayment\Helper\Data
     */
    protected function getCheckoutHelper()
    {
        return $this->checkoutHelper;
    }

    protected function _initService()
    {
        $quote = $this->getQuote();

        if (!$this->_service) {
            $parameters = [
                'params' => [
                    'quote' => $quote,
//                    'config' => $this->_config,
                ],
            ];
            $this->_service = $this->_tendoService->create($parameters);
        }
    }
}
