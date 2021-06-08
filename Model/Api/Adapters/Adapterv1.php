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

namespace TendoPay\TendopayPayment\Model\Api\Adapters;

/**
 * Class Adapterv1
 * @package TendoPay\TendopayPayment\Model\Api\Adapters
 */
class Adapterv1
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Adapterv1 constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $object
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildOrderTokenRequest($object)
    {
        $precision = 2;
        $this->_validateData($object);
        $shippingAddress = $object->getShippingAddress();
        $taxTotal = 0;
        $params['items'] = [];

        foreach ($object->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $params['items'][] = [
                'title' => (string)$item->getName(),
                'description' =>
                    (string)($product->getShortDescription() ? $product->getShortDescription() :
                        $product->getDescription()),
                'SKU' => $this->_truncateString((string)$item->getSku()),
                'price' => round((float)$item->getPriceInclTax(), $precision)
            ];

            $discountAmount = $item->getDiscountAmount();
            if (!empty($discountAmount) && round((float)$discountAmount, $precision) > 0) {
                $discountName = (string)$object->getCouponCode();
                if (empty($discountName) || strlen(trim($discountName)) == '') {
                    $discountName = 'Discount:';
                }

                $params['discounts'][] = [
                    'displayName' => substr($discountName . ' - ' . (string)$item->getName(), 0, 128),
                    'amount' => [
                        'amount' => round((float)$item->getDiscountAmount(), $precision),
                        'currency' => (string)$this->storeManager->getStore()->getCurrentCurrency()->getCode()
                    ],
                ];
            }

            $taxTotal += $item->getTaxAmount();
        }

        $params['meta'] = [
            'currency' => (string)$this->storeManager->getStore()->getCurrentCurrency()->getCode(),
            'thousand_separator' => ",",
            'decimal_separator' => ".",
            'version' => 1
        ];

        $params['order'] = [
            'id' => (string)$this->storeManager->getStore()->getCurrentCurrency()->getCode(),
            'shipping' => ($shippingAddress) ? round((float)$shippingAddress->getShippingInclTax(), $precision) : 0,
            'subtotal' => $object->getSubtotal(),
            'total' => round((float)$object->getGrandTotal(), $precision)
        ];
        if (!empty($object) && $object->getId()) {
            $params['merchantReference'] = (string)$object->getId();
        }

        return $params;
    }

    /**
     * @param $string
     * @param int $length
     * @param string $appendStr
     * @return string
     */
    public function _truncateString($string, $length = 64, $appendStr = "")
    {
        $truncatedStr = "";
        $useAppendStr = (strlen($string) > (int)($length)) ? true : false;
        $truncatedStr .= substr($string, 0, $length);
        $truncatedStr .= ($useAppendStr) ? $appendStr : "";
        return $truncatedStr;
    }

    /**
     * @param $object
     */
    public function _handleState($object)
    {
        $billingCountry = $object->getBillingAddress()->getCountry();
        if (!empty($billingCountry)) {
            $listStateRequired = $this->_getStateRequired();
            if (!in_array($billingCountry, $listStateRequired)) {
                $object->getBillingAddress()->setRegion($object->getBillingAddress()->getCity())->save();
                $object->getShippingAddress()->setRegion($object->getShippingAddress()->getCity())->save();
            }
        }
    }

    /**
     * @return array
     */
    public function _getStateRequired()
    {
        $destinations = $this->scopeConfig->getValue(
            'general/region/state_required',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $stateRequired = !empty($destinations) ? explode(',', $destinations) : [];
        return $stateRequired;
    }

    /**
     * @param $object
     */
    public function _validateData($object)
    {
        $errors = [];
        $this->_handleState($object);
        $billingAddress = $object->getBillingAddress();
        $shippingAddress = $object->getShippingAddress();

        if (empty($billingAddress->getPostcode())) {
            $errors[] = "Billing Postcode is required";
        }

        if (empty($billingAddress->getRegion())) {
            $errors[] = "Billing State is required";
        }

        if (empty($billingAddress->getTelephone())) {
            $errors[] = "Billing Phone is required";
        }

        if (empty($billingAddress->getCity())) {
            $errors[] = "Billing City/Suburb is required";
        }

        if (empty($billingAddress->getStreet())) {
            $errors[] = "Billing Address is required";
        }

        if (!empty($shippingAddress)) {
            $errors=$this->_verifyShipping($shippingAddress, $errors);
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException("<br/>" . implode($errors, '<br/>'));
        }
    }

    /**
     * @param $shippingAddress
     * @param $errors
     * @return array
     */
    public function _verifyShipping($shippingAddress, $errors)
    {
        if (empty($shippingAddress->getPostcode())) {
            $errors[] = "Shipping Postcode is required";
        }

        if (empty($shippingAddress->getRegion())) {
            $errors[] = "Shipping State is required";
        }

        if (empty($shippingAddress->getTelephone())) {
            $errors[] = "Shipping Phone is required";
        }

        if (empty($shippingAddress->getCity())) {
            $errors[] = "Shipping City/Suburb is required";
        }

        if (empty($shippingAddress->getStreet())) {
            $errors[] = "Shipping Address is required";
        }

        return $errors;
    }
}
