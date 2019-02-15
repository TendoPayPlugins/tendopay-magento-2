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

use \TendoPay\TendopayPayment\Helper\Data as TendoPayHelper;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\App\RequestInterface;

/**
 * Class ConfigProvider
 * @package TendoPay\TendopayPayment\Model
 */
class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var string
     */
    protected $methodCode = TendoPayHelper::METHOD_WPS;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $method;

    /**
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Standard
     */
    protected $tendopay;

    /**
     * ConfigProvider constructor.
     * @param \Magento\Payment\Helper\Data $paymenthelper
     * @param AssetRepository $assetRepository
     * @param RequestInterface $request
     * @param Standard $tendopay
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymenthelper,
        AssetRepository $assetRepository,
        RequestInterface $request,
        \TendoPay\TendopayPayment\Model\Standard $tendopay
    ) {
        $this->method = $paymenthelper->getMethodInstance($this->methodCode);
        $this->assetRepository = $assetRepository;
        $this->request = $request;
        $this->tendopay = $tendopay;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $params = ['_secure' => $this->request->isSecure()];
        $visibility = false;
        if (!empty($this->tendopay->getConfigData('api_merchant_id')) &&
            !empty($this->tendopay->getConfigData('api_merchant_secret')) &&
            !empty($this->tendopay->getConfigData('api_client_id')) &&
            !empty($this->tendopay->getConfigData('api_client_secret'))
        ) {
            $visibility = true;
        }

        return $this->method->isAvailable() ? [
            'payment'=>[
                'tendopay'=> [
                    'visibility' => $visibility,
                    'redirectUrl' => $this->tendopay->getRedirectUrl(),
                    'paymentAcceptanceMarkSrc' => $this->assetRepository->getUrlWithParams(
                        'TendoPay_TendopayPayment::images/tendopay.png',
                        $params
                    ),
                    'paymentAcceptanceMarkHref' => TendoPayHelper::TENDO_PAY_FAQ_URL,
                    'paymentAcceptanceMarkMessage' => $this->tendopay->getConfigData('message')
                ]
            ]
        ]:[];
    }
}
