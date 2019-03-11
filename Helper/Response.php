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

/**
 * Class Response
 * @package TendoPay\TendopayPayment\Helper
 */
class Response extends AbstractHelper
{
    /**
     * @var Response
     */
    private $body;

    /**
     * @var Response
     */
    private $code;

    /**
     * Response constructor.
     * @param $code
     * @param $body
     */
    public function __construct($code, $body)
    {
        $this->body = $body;
        $this->code = $code;
    }

    /**
     * @return Response
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return Response
     */
    public function getCode()
    {
        return $this->code;
    }
}
