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

namespace TendoPay\TendopayPayment\Model\System\Config\Source;

/**
 * Class ApiMode
 * @package TendoPay\TendopayPayment\Model\System\Config\Source
 */
class InterestType implements \Magento\Framework\Option\ArrayInterface
{
    const INTEREST_TYPE_REGULAR = 'regular';
    const INTEREST_TYPE_ZERO    = 'zero';

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::INTEREST_TYPE_REGULAR,
                'label' => __('Pay in installments')
            ],
            [
                'value' => self::INTEREST_TYPE_ZERO,
                'label' => __('Pay in 4 ez installments'),
            ],
        ];
    }
}
