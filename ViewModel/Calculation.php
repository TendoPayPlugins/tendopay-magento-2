<?php
namespace TendoPay\TendopayPayment\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;
use TendoPay\SDK\V2\TendoPayClient;
use TendoPay\TendopayPayment\Model\System\Config\Source\InterestType;
use Magento\Directory\Model\CurrencyFactory;

class Calculation implements ArgumentInterface
{
    protected $scopeConfig;
    protected $httpContext;
    protected $tendopayAmount = null;
    protected $totalAmount = null;
    protected $totalInstallments = null;
    protected $installmentAmount = null;

    /**
     * @var TendoPayClient
     */
    private $tendoPayClient;
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Used for GuzzleHttp client $client
     */
    protected $client;
    /**
     * @var \TendoPay\TendopayPayment\Helper\Data
     */
    private $helper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    protected $currentCurrencySymbol;

    /**
     * Calculation constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param HttpContext $httpContext
     * @param TendoPayClient $tendoPayClient
     * @param Curl $curl
     * @param SerializerInterface $jsonSerializer
     * @param LoggerInterface $logger
     * @param \TendoPay\TendopayPayment\Helper\Data $helper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HttpContext $httpContext,
        TendoPayClient $tendoPayClient,
        Curl $curl,
        SerializerInterface $jsonSerializer,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        \TendoPay\TendopayPayment\Helper\Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->httpContext = $httpContext;
        $this->tendoPayClient = $tendoPayClient;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
    }

    public function getCurrentCurrencySymbol()
    {
        if(is_null($this->currentCurrencySymbol)) {
            $currentCurrencyCode = $this->storeManager->getStore()->getData('current_currency')->getCurrencyCode();
            $this->currentCurrencySymbol = $this->currencyFactory->create()->load($currentCurrencyCode)->getCurrencySymbol();
        }
        return $this->currentCurrencySymbol;
    }

    public function clean()
    {
        $this->tendopayAmount = null;
        $this->totalAmount = null;
        $this->totalInstallments = null;
        $this->installmentAmount = null;

        return $this;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return $this
     */
    public function load($product)
    {
        $this->clean();

        $price = $product->getFinalPrice();
        if(!$price) return $this;

        $url = $this->helper->getBaseApiUrlCalculator() . "?tendopay_amount={$price}";

        if(InterestType::INTEREST_TYPE_REGULAR == $this->helper->getInterestType()) {
            $url = "{$url}&payin4=true";
        }
        if($data = $this->getData($url)) {
            $this->tendopayAmount = $data['tendopay_amount'];
            $this->totalAmount = $data['tendopay_amount'];
            $this->totalInstallments = $data['total_installments'];
            $this->installmentAmount = $data['installment_amount'];
        }

        return $this;
    }

    public function getTendopayAmount()
    {
        return $this->tendopayAmount;
    }

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    public function getTotalInstallments()
    {
        return $this->totalInstallments;
    }

    public function getInstallmentAmount()
    {
        return $this->installmentAmount;
    }

    public function getData($url)
    {
        try {

            $this->curl->get($url);
            if($this->curl->getStatus() == 200) {
                if($resultJson = $this->curl->getBody()) {
                    $retult = $this->jsonSerializer->unserialize($resultJson);
                    return $retult['data']??null;
                }
            }

        }catch (\Exception $exception) {
            $this->logger->error($exception);
        }

        return false;
    }
}
