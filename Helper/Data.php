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

namespace TendoPay\TendopayPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Data
 * @package TendoPay\TendopayPayment\Helper
 */
class Data extends AbstractHelper
{
    const PAYMANET_FAILED_QUERY_PARAM = 'tendopay_payment_failed';
    const METHOD_WPS = 'tendopay';
    const REDIRECT_URL_PATTERN = '^tendopay-result/?';
    const HASH_ALGORITHM = 'sha256';

    /**
     * Below constant names are used as live TP API
     */
    const BASE_API_URL = 'https://app.tendopay.ph/';
    const REDIRECT_URI = 'https://app.tendopay.ph/payments/authorise';
    const VIEW_URI_PATTERN = 'https://app.tendopay.ph/view/transaction/%s';
    const VERIFICATION_ENDPOINT_URI = 'payments/api/v1/verification';
    const AUTHORIZATION_ENDPOINT_URI = 'payments/api/v1/authTokenRequest';
    const DESCRIPTION_ENDPOINT_URI = 'payments/api/v1/paymentDescription';
    const BEARER_TOKEN_ENDPOINT_URI = 'oauth/token';

    /**
     * Below constant names are used as sandbox TP API
     */
    const SANDBOX_BASE_API_URL = 'https://sandbox.tendopay.ph/';
    const SANDBOX_REDIRECT_URI = 'https://sandbox.tendopay.ph/payments/authorise';
    const SANDBOX_VIEW_URI_PATTERN = 'https://sandbox.tendopay.ph/view/transaction/%s';
    const SANDBOX_VERIFICATION_ENDPOINT_URI = 'payments/api/v1/verification';
    const SANDBOX_AUTHORIZATION_ENDPOINT_URI = 'payments/api/v1/authTokenRequest';
    const SANDBOX_DESCRIPTION_ENDPOINT_URI = 'payments/api/v1/paymentDescription';
    const SANDBOX_BEARER_TOKEN_ENDPOINT_URI = 'oauth/token';

    /**
     * Below constant names are used as keys of data send to or received from TP API
     */
    const AMOUNT_PARAM = 'tendopay_amount';
    const AUTH_TOKEN_PARAM = 'tendopay_authorisation_token';
    const TENDOPAY_CUSTOMER_REFERENCE_1 = 'tendopay_customer_reference_1';
    const TENDOPAY_CUSTOMER_REFERENCE_2 = 'tendopay_customer_reference_2';
    const REDIRECT_URL_PARAM = 'tendopay_redirect_url';
    const VENDOR_ID_PARAM = 'tendopay_tendo_pay_vendor_id';
    const VENDOR_PARAM = 'tendopay_vendor';
    const HASH_PARAM = 'tendopay_hash';
    const DISPOSITION_PARAM = 'tendopay_disposition';
    const TRANSACTION_NO_PARAM = 'tendopay_transaction_number';
    const VERIFICATION_TOKEN_PARAM = 'tendopay_verification_token';
    const DESC_PARAM = 'tendopay_description';
    const STATUS_PARAM = 'tendopay_status';
    const USER_ID_PARAM = 'tendopay_user_id';

    const TENDO_PAY_FAQ_URL = 'https://tendopay.ph/page-faq.html';

    /**
     * Below constants are the keys of description object that is being sent during request to Description Endpoint
     */
    const ITEMS_DESC_PROPNAME = 'items';
    const META_DESC_PROPNAME = 'meta';
    const ORDER_DESC_PROPNAME = 'order';

    /**
     * Below constants are the keys of description object's line items that
     * are being sent during request to Description Endpoint
     */
    const TITLE_ITEM_PROPNAME = 'title';
    const DESC_ITEM_PROPNAME = 'description';
    const SKU_ITEM_PROPNAME = 'SKU';
    const PRICE_ITEM_PROPNAME = 'price';

    /**
     * Below constants are the keys of description object's meta info that
     * is being sent during request to Description Endpoint
     */
    const CURRENCY_META_PROPNAME = 'currency';
    const THOUSAND_SEP_META_PROPNAME = 'thousand_separator';
    const DECIMAL_SEP_META_PROPNAME = 'decimal_separator';
    const VERSION_META_PROPNAME = 'version';

    /**
     * Below constants are the keys of description object's order details that
     * are being sent during request to Description Endpoint
     */
    const ID_ORDER_PROPNAME = 'id';
    const SUBTOTAL_ORDER_PROPNAME = 'subtotal';
    const TOTAL_ORDER_PROPNAME = 'total';

    const TEMPLATE_OPTION_TITLE_CUSTOM = 'tendopay/payment/title.phtml';

    /**
     * Marketing label constants
     */
    const TENDOPAY_LOGO_BLUE = 'https://s3-ap-southeast-1.amazonaws.com/tendo-static/logo/tp-logo-example-payments.png';
    const TENDOPAY_MARKETING = 'https://app.tendopay.ph/register';
    const REPAYMENT_SCHEDULE_API_ENDPOINT_URI = "payments/api/v1/repayment-calculator?tendopay_amount=%s";
    const REPAYMENT_CALCULATOR_INSTALLMENT_AMOUNT = 'installment_amount';

    /* Configuration fields */
    const API_ENABLED_FIELD = 'payment/tendopay/active';
    const API_MODE_CONFIG_FIELD = 'payment/tendopay/api_mode';
    const API_MIN_ORDER_TOTAL_FIELD = 'min_order_total';
    const API_MAX_ORDER_TOTAL_FIELD = 'max_order_total';
    const API_URL_CONFIG_PATH_PATTERN = 'tendopay/api/{prefix}_api_url';
    const WEB_URL_CONFIG_PATH_PATTERN = 'tendopay/api/{prefix}_web_url';
    const API_MERCHANT_ID_CONFIG_FIELD = 'payment/tendopay/api_merchant_id';
    const API_BEARER_TOKEN_FIELD = 'payment/tendopay/bearer_token';
    const API_MERCHANT_SECRET_CONFIG_FIELD = 'payment/tendopay/api_merchant_secret';
    const API_CLIENT_ID_CONFIG_FIELD = 'payment/tendopay/api_client_id';
    const API_CLIENT_SECRET_CONFIG_FIELD = 'payment/tendopay/api_client_secret';
    const OPTION_TENDOPAY_EXAMPLE_INSTALLMENTS_ENABLE = 'payment/tendopay/tendo_example_installments_enabled';

    /* Order payment statuses */
    const RESPONSE_STATUS_APPROVED = 'APPROVED';
    const RESPONSE_STATUS_PENDING = 'pending';
    const RESPONSE_STATUS_FAILED = 'FAILED';
    const RESPONSE_STATUS_DECLINED = 'DECLINED';
    const RESPONSE_STATUS_PROCESSING = 'processing';
    const TRUNCATE_SKU_LENGTH = 128;

    /**
     * @var string $bearerToken the bearer token requested in previous API calls. If it's null, it will be taken from
     * wordpress options. If it was null or expired in the options, it will be then requested from the API.
     */
    private static $_bearerToken;

    /**
     * @var string
     */
    protected $_logFileName = 'tendopay.log';
    /**
     * @var
     */
    protected $_isDebugEnabled;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \TendoPay\TendopayPayment\Model\Api\Adapters\Adapterv1
     */
    protected $adapterv1;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \TendoPay\TendopayPayment\Logger\Logger
     */
    protected $tendopayLogger;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    public $serializer;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;

    /**
     * @var Used for GuzzleHttp client $client
     */
    protected $client;

    /**
     * Data constructor.
     * @param \TendoPay\TendopayPayment\Model\Api\Adapters\Adapterv1 $adapterv1
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \TendoPay\TendopayPayment\Logger\Logger $tendopayLogger
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param Context $context
     */
    public function __construct(
        \TendoPay\TendopayPayment\Model\Api\Adapters\Adapterv1 $adapterv1,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Quote\Model\QuoteFactory $quote,
        \TendoPay\TendopayPayment\Logger\Logger $tendopayLogger,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        Context $context
    ) {
        parent::__construct($context);
        $this->adapterv1 = $adapterv1;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->checkoutSession = $checkoutSession;
        $this->quote = $quote;
        $this->messageManager = $messageManager;
        $this->tendopayLogger = $tendopayLogger;
        $this->productMetadata = $productMetadata;
        $this->serializer = $serializer;
    }

    /**
     * @return string
     */
    public function getRepaymentCalculatorInstallmentAmount()
    {
        return self::REPAYMENT_CALCULATOR_INSTALLMENT_AMOUNT;
    }

    /**
     * @return string
     */
    public function getRepaymentScheduleApiEndpointUri()
    {
        return self::REPAYMENT_SCHEDULE_API_ENDPOINT_URI;
    }

    /**
     * @return string
     */
    public function getTendopayCheckoutTitle()
    {
        return self::TEMPLATE_OPTION_TITLE_CUSTOM;
    }

    /**
     * @return string
     */
    public function getTendopayMethodCode()
    {
        return self::METHOD_WPS;
    }

    /**
     * Gets the hash algorithm.
     *
     * @return string hash algorithm
     */
    public function getHashAlgorithm()
    {
        return self::HASH_ALGORITHM;
    }

    /**
     * Gets the base api URL. It checks whether to use SANDBOX URL or Production URL.
     *
     * @return string the base api url
     */
    public function getBaseApiUrl()
    {
        return $this->isSandboxEnabled() ? self::SANDBOX_BASE_API_URL : self::BASE_API_URL;
    }

    /**
     * Gets the verification endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string verification endpoint uri
     */
    public function getVerificationEndpointUri()
    {
        return $this->isSandboxEnabled() ? self::SANDBOX_VERIFICATION_ENDPOINT_URI : self::VERIFICATION_ENDPOINT_URI;
    }

    /**
     * Gets the authorization endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string authorization endpoint uri
     */
    public function getAuthorizationEndpointUri()
    {
        return $this->isSandboxEnabled() ? self::SANDBOX_AUTHORIZATION_ENDPOINT_URI : self::AUTHORIZATION_ENDPOINT_URI;
    }

    /**
     * Gets the description endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string description endpoint uri
     */
    public function getDescriptionEndpointUri()
    {
        return $this->isSandboxEnabled() ? self::SANDBOX_DESCRIPTION_ENDPOINT_URI : self::DESCRIPTION_ENDPOINT_URI;
    }

    /**
     * Gets the bearer token endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string bearer token endpoint uri
     */
    public function getBearerTokenEndpointUri()
    {
        return $this->isSandboxEnabled() ? self::SANDBOX_BEARER_TOKEN_ENDPOINT_URI : self::BEARER_TOKEN_ENDPOINT_URI;
    }

    /**
     * Gets the bearer token endpoint uri. It checks whether to use SANDBOX URI or Production URI.
     *
     * @return string bearer token endpoint uri
     */
    public function getRepaymentCalculatorApiEndpointUrl()
    {
        $baseUrl = $this->isSandboxEnabled() ? self::SANDBOX_BASE_API_URL : self::BASE_API_URL;
        return $baseUrl . "/" . self::REPAYMENT_SCHEDULE_API_ENDPOINT_URI;
    }
    /**
     *
     * @return bool true if sandbox is enabled
     */
    public function isSandboxEnabled()
    {
        $isSanboxEnabled = $this->getConfigValues(self::getAPIModeConfigField());
        if ($isSanboxEnabled == "sandbox") {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getVendorIdParam()
    {
        return self::VENDOR_ID_PARAM;
    }

    /**
     * @return string
     */
    public function getHashParam()
    {
        return self::HASH_PARAM;
    }

    /**
     * @return string
     */
    public function getVendorParam()
    {
        return self::VENDOR_PARAM;
    }

    /**
     * @return string
     */
    public function getRedirectUrlParam()
    {
        return self::REDIRECT_URL_PARAM;
    }

    /**
     * @return string
     */
    public function getTendopayCustomerReferenceOne()
    {
        return self::TENDOPAY_CUSTOMER_REFERENCE_1;
    }

    /**
     * @return string
     */
    public function getTendopayCustomerReferencetwo()
    {
        return self::TENDOPAY_CUSTOMER_REFERENCE_2;
    }

    /**
     * @return string
     */
    public function getDispositionParam()
    {
        return self::DISPOSITION_PARAM;
    }

    /**
     * @return string
     */
    public function getTransactionNoParam()
    {
        return self::TRANSACTION_NO_PARAM;
    }

    /**
     * @return string
     */
    public function getVerificationTokenParam()
    {
        return self::VERIFICATION_TOKEN_PARAM;
    }

    /**
     * @return string
     */
    public function getUserIDParam()
    {
        return self::USER_ID_PARAM;
    }

    /**
     * @return string
     */
    public function getStatusIDParam()
    {
        return self::STATUS_PARAM;
    }

    /**
     * @return string
     */
    public function getAuthTokenParam()
    {
        return self::AUTH_TOKEN_PARAM;
    }

    /**
     * @return string
     */
    public function getAmountParam()
    {
        return self::AMOUNT_PARAM;
    }

    /**
     * @return string
     */
    public function getDescParam()
    {
        return self::DESC_PARAM;
    }

    /**
     * @return string
     */
    public function paymentFailedQueryParam()
    {
        return self::PAYMANET_FAILED_QUERY_PARAM;
    }

    /**
     * @param $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    /**
     * @return mixed
     */
    public function getCheckoutUrl()
    {
        return $this->_getUrl('checkout/cart/index', ['_secure' => true]);
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->_getUrl('tendopay/standard/success', ['_secure' => true]);
    }

    /**
     * @return mixed
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @param $message
     */
    public function addTendopayError($message)
    {
        $this->messageManager->addErrorMessage($message);
    }

    /**
     * @param $message
     * @param string $type
     */
    public function addTendopayLog($message, $type = 'warning')
    {
        $dubug = $this->getConfigValues('payment/tendopay/debug');
        if ($dubug) {
            switch ($type) {
                case 'info':
                    $this->tendopayLogger->addInfo($message);
                    break;
                case 'warning':
                    $this->tendopayLogger->addWarning($message);
                    break;
                case 'notice':
                    $this->tendopayLogger->addNotice($message);
                    break;
                case 'error':
                    $this->tendopayLogger->addError($message);
                    break;
            }
        }
    }

    /**
     * @return string
     */
    public function getGmtDate()
    {
        return $this->dateTime->gmtDate();
    }

    /**
     * @return string
     */
    public function getFormateDate($input, $format)
    {
        return $this->dateTime->date($format, $input);
    }

    /**
     * @return string
     */
    public static function getAPIEnabledField()
    {
        return self::API_ENABLED_FIELD;
    }

    /**
     * @return string
     */
    public static function getMinOrderTotalField()
    {
        return self::API_MIN_ORDER_TOTAL_FIELD;
    }

    /**
     * @return string
     */
    public static function getMaxOrderTotalField()
    {
        return self::API_MAX_ORDER_TOTAL_FIELD;
    }

    /**
     * @return string
     */
    public static function getAPIModeConfigField()
    {
        return self::API_MODE_CONFIG_FIELD;
    }

    /**
     * @return string
     */
    public static function getAPIMerchantIDConfigField()
    {
        return self::API_MERCHANT_ID_CONFIG_FIELD;
    }

    /**
     * @return string
     */
    public static function getBearerTokenConfigField()
    {
        return self::API_BEARER_TOKEN_FIELD;
    }

    /**
     * @return string
     */
    public static function getAPIMerchantSecretConfigField()
    {
        return self::API_MERCHANT_SECRET_CONFIG_FIELD;
    }

    /**
     * @return string
     */
    public static function getAPIClientIdConfigField()
    {
        return self::API_CLIENT_ID_CONFIG_FIELD;
    }

    /**
     * @return string
     */
    public static function getAPIClientSecretConfigField()
    {
        return self::API_CLIENT_SECRET_CONFIG_FIELD;
    }

    /**
     * @return string
     */
    public static function getTendoExampleInstallmentsEnabled()
    {
        return self::OPTION_TENDOPAY_EXAMPLE_INSTALLMENTS_ENABLE;
    }
    /**
     * @return string
     */
    public function getCgiUrl()
    {
        $env = $this->getConfigValues(self::getAPIModeConfigField());
        if ($env === 'production') {
            return self::REDIRECT_URI;
        }
        return self::SANDBOX_REDIRECT_URI;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function restoreQuote()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            $quote = $this->quote->create()->load($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(1)
                    ->setReservedOrderId(null)
                    ->save();
                $this->checkoutSession->replaceQuote($quote)->unsLastRealOrderId();
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getApiAdapter()
    {
        return $this->adapterv1;
    }

    /**
     * @param $field
     * @return mixed
     */
    public function getConfigValues($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getTendopayLogoBlue()
    {
        return self::TENDOPAY_LOGO_BLUE;
    }

    /**
     * @return string
     */
    public function getTendopayMarketing()
    {
        return self::TENDOPAY_MARKETING;
    }

    /**
     * Compare magento version
     *
     * @param $ver
     * @return mixed
     */
    public function versionCompare($ver)
    {
        //will return the magento version
        $version = $this->productMetadata->getVersion();
        return version_compare($version, $ver, '>=');
    }

    /**
     * @param array $data
     * @return string
     */
    public function calculate(array $data)
    {
        $secret = $this->getConfigValues(self::getAPIMerchantSecretConfigField());
        $data = array_map(
            function ($value) {
                return trim($value);
            },
            $data
        );

        $hashKeysExclusionList = [$this->getHashParam()];
        $exclusionList = $hashKeysExclusionList;
        $data = $this->arrayFilterKeys($data, $exclusionList);

        ksort($data);
        $message = join("", $data);
        return hash_hmac($this->getHashAlgorithm(), $message, $secret, false);
    }

    /**
     * Exclude specific parameter from array
     *
     * @param $array
     * @param $exclusionList
     * @return array
     */
    public function arrayFilterKeys($array, $exclusionList)
    {
        $newArray= [];
        foreach ($array as $key => $value) {
            if (!in_array($key, $exclusionList) && !empty($value)) {
                $newArray[$key]=$value;
            }
        }
        return $newArray;
    }

    /**
     * Call API to specific URL
     *
     * @param $url
     * @param array $data
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doCall($url, array $data)
    {
        $merchantId = $this->getConfigValues(self::getAPIMerchantIDConfigField());
        $data[$this->getVendorIdParam()] = $merchantId;
        $data[$this->getHashParam()] = $this->calculate($data);

        $headers = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getBearerToken(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Using' => 'TendoPay Magento2 Extension',
            ],
            'json' => $data
        ];
        $this->client = new \GuzzleHttp\Client(
            [
                'base_uri' => $this->getBaseApiUrl()
            ]
        );
        $response = $this->client->request('POST', $url, $headers);
        return new \TendoPay\TendopayPayment\Helper\Response($response->getStatusCode(), $response->getBody());
    }

    /**
     * Call API to get Bearer token
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getBearerToken()
    {

        if (self::$_bearerToken === null) {
            $bearerTokenConfigField = $this->getConfigValues(self::getBearerTokenConfigField());
            self::$_bearerToken = $bearerTokenConfigField;
        }

        $bearerExpirationTimestamp = -1;
        if (self::$_bearerToken !== null && property_exists(self::$_bearerToken, 'expiration_timestamp')) {
            $bearerExpirationTimestamp = self::$_bearerToken->expiration_timestamp;
        }

        $currentTimestamp = $this->dateTime->gmtTimestamp();
        if ($bearerExpirationTimestamp <= $currentTimestamp - 30) {
            $headers = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Using' => 'TendoPay Magento2 Extension'
                ],
                'json' => [
                    "grant_type" => "client_credentials",
                    "client_id" => $this->getConfigValues(self::getAPIClientIdConfigField()),
                    "client_secret" => $this->getConfigValues(self::getAPIClientSecretConfigField())
                ]
            ];

            $this->client = new \GuzzleHttp\Client(
                [
                    'base_uri' => $this->getBaseApiUrl()
                ]
            );
            $response = $this->client->request('POST', $this->getBearerTokenEndpointUri(), $headers);
            $responseBody = (string)$response->getBody();
            $responseBody = json_decode($responseBody);

            self::$_bearerToken = new \stdClass();
            self::$_bearerToken->expiration_timestamp = $responseBody->expires_in + $currentTimestamp;
            self::$_bearerToken->token = $responseBody->access_token;

            $this->configWriter->save(
                'payment/tendopay/bearer_token',
                $this->serializer->serialize(self::$_bearerToken),
                $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $scopeId = 0
            );
        }

        return self::$_bearerToken->token;
    }

    public function getDefaultHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getBearerToken(),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'X-Using'       => 'TendoPay Magento2 Plugin',
        ];
    }
}
