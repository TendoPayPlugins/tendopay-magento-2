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
class InstallmentsRetriever extends AbstractHelper
{
    private $product_price;

    /**
     * @var RepaymentCalculatorEndpoint
     */
    public $repaymentCalculator;

    /**
     * InstallmentsRetriever constructor.
     * @param Context $context
     * @param RepaymentCalculatorEndpoint $repaymentCalculator
     */
    public function __construct(
        Context $context,
        \TendoPay\TendopayPayment\Helper\RepaymentCalculatorEndpoint $repaymentCalculator
    ) {
        $this->repaymentCalculator = $repaymentCalculator;
        parent::__construct($context);
    }

    /**
     * @param $amount
     * @return mixed
     * @throws TendoPay_Integration_Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getExamplePayment($amount)
    {
        $installmentAmount = $this->repaymentCalculator->getInstallmentAmount($amount);
        return $installmentAmount;
    }
}
