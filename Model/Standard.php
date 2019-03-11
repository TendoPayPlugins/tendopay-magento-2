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

namespace TendoPay\TendopayPayment\Model;

use Magento\Directory\Helper\Data as DirectoryHelper;
use \Magento\Payment\Model\Method\Logger;
use \TendoPay\TendopayPayment\Helper\Data as TendoPayHelper;

/**
 * Class Standard
 * @package TendoPay\TendopayPayment\Model
 */
class Standard extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = TendoPayHelper::METHOD_WPS;

    /**
     * @var string
     */
    protected $_infoBlockType = '\TendoPay\TendopayPayment\Block\Payment\Info';

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = false;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * @var TendoPayHelper
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $checkoutSession;

    /**
     * Standard constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param TendoPayHelper $helper
     * @param \Magento\Sales\Model\OrderFactory $salesOrder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Logger $logger,
        TendoPayHelper $helper,
        \Magento\Sales\Model\OrderFactory $salesOrder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );
        $this->helper = $helper;
        $this->salesOrder = $salesOrder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $authToken
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildCheckoutRequest($authToken)
    {
        /** @var \Magento\Checkout\Model\Session checkoutSession */
        $this->checkoutSession = $this->helper->getCheckoutSession();
        $order = $this->checkoutSession->getLastRealOrder();
        $getLastRealOrderId = $this->checkoutSession->getLastRealOrderId();
        $params = [];
        if ($order) {
            $merchantId = $this->helper->getConfigValues($this->helper->getAPIMerchantIDConfigField());
            $store = $this->storeManager->getStore();

            $redirectArgs = [
                $this->helper->getAmountParam() => (int)$order->getGrandTotal(),
                $this->helper->getAuthTokenParam() => $authToken,
                $this->helper->getTendopayCustomerReferenceOne() => (string)$getLastRealOrderId,
                $this->helper->getTendopayCustomerReferencetwo() => "magento2_order_" . $getLastRealOrderId,
                $this->helper->getRedirectUrlParam() => $this->helper->getRedirectUrl(),
                $this->helper->getVendorIdParam() => $merchantId,
                $this->helper->getVendorParam() => $store->getName()
            ];

            $redirectArgsHash = $this->helper->calculate($redirectArgs);
            $redirectArgs[$this->helper->getHashParam()] = $redirectArgsHash;
            $redirectArgs["er"] = $this->helper->getCheckoutUrl();

            $params["fields"] = $redirectArgs;
            $params["url"] = $this->helper->getCgiUrl().'?' .
                http_build_query($redirectArgs).
                '&er=' . urlencode($this->helper->getCheckoutUrl());
        }
        return $params;
    }

    /**
     * @return bool
     */
    public function resetTransactionToken()
    {
        $this->checkoutSession = $this->helper->getCheckoutSession();
        $this->checkoutSession->getQuote()->getPayment()->setData(
            'tendopay_token',
            null
        )->save();
        return true;
    }

    /**
     * Return redirect url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->helper->getUrl($this->getConfigData('redirect_url'));
    }

    /**
     * Get config payment action, do nothing if status is pending
     *
     * @return string|null
     */
    public function getConfigPaymentAction()
    {
        return $this->getConfigData('order_status') == 'pending' ? null : parent::getConfigPaymentAction();
    }
}
