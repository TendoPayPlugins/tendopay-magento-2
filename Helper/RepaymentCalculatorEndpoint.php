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

/**
 * Class Data
 * @package TendoPay\TendopayPayment\Helper
 */
class RepaymentCalculatorEndpoint extends AbstractHelper
{
    /**
     * @var Data
     */
    public $helper;

    protected $client;

    /**
     * RepaymentCalculatorEndpoint constructor.
     * @param Context $context
     * @param Data $tendopayHelper
     */
    public function __construct(
        Context $context,
        \TendoPay\TendopayPayment\Helper\Data $tendopayHelper
    ) {
        $this->helper = $tendopayHelper;
        parent::__construct($context);
    }

    public function getInstallmentAmount($amount)
    {
        $amount = (double) $amount;
        $hash = $this->helper->calculate([$amount]);
        $url = sprintf($this->helper->getRepaymentScheduleApiEndpointUri(), $amount);

        $response = null;
        try {
            $this->client = new \GuzzleHttp\Client(
                [
                    'base_uri' => $this->helper->getBaseApiUrl()
                ]
            );
            $response = $this->client->request("GET", $url, ["headers" => $this->helper->getDefaultHeaders()]);
        } catch (\Exception $exception) {
            return '';
        }

        if ($response->getStatusCode() !== 200) {
            $this->helper->addTendopayLog('Got response code != 200 while requesting for payment calculation', 'error');
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Got response code != 200 while requesting for payment calculation', $response->getCode())
            );
        }
        $json = json_decode((string) $response->getBody());
        return $json->data->{$this->helper->getRepaymentCalculatorInstallmentAmount()};
    }
}
