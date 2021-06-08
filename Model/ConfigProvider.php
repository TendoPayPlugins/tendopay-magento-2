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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Gateway\ConfigInterface;
use TendoPay\TendopayPayment\Helper\Data as TendoPayHelper;

/**
 * Class ConfigProvider
 * @package TendoPay\TendopayPayment\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'tendopay';

    /**
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    public $url;
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * ConfigProvider constructor.
     * @param AssetRepository $assetRepository
     * @param UrlInterface $url
     * @param ConfigInterface $config
     * @param RequestInterface $request
     */
    public function __construct(
        AssetRepository $assetRepository,
        UrlInterface $url,
        ConfigInterface $config,
        RequestInterface $request
    ) {
        $this->assetRepository = $assetRepository;
        $this->url = $url;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $params = ['_secure' => $this->request->isSecure()];
        $visibility = false;
        if (!empty($this->config->getValue('api_client_id')) &&
            !empty($this->config->getValue('api_client_secret'))
        ) {
            $visibility = true;
        }

        return $this->config->getValue('active') ? [
            'payment' => [
                'tendopay' => [
                    'visibility' => $visibility,
                    'redirectUrl' => $this->url->getUrl($this->config->getValue('redirect_url'), $params),
                    'paymentAcceptanceMarkSrc' => $this->assetRepository->getUrlWithParams(
                        'TendoPay_TendopayPayment::images/tendopay.png',
                        $params
                    ),
                    'paymentAcceptanceMarkHref' => TendoPayHelper::TENDO_PAY_FAQ_URL,
                    'paymentAcceptanceMarkMessage' => $this->config->getValue('message'),
                    'tendopayMarketingPopup' => $this->url->getUrl('tendopay/standard/popupbox'),
                    'minTotalAmount' => $this->config->getValue('min_total_amount'),
                ]
            ]
        ] : [];
    }
}
