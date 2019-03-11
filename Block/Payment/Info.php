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

namespace TendoPay\TendopayPayment\Block\Payment;

use Magento\Framework\View\Element\Template;

/**
 * Class Info
 * @package TendoPay\TendopayPayment\Block\Payment
 */
class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var \TendoPay\TendopayPayment\Helper\Data
     */
    private $tendopayHelper;

    /**
     * Info constructor.
     * @param Template\Context $context
     * @param \TendoPay\TendopayPayment\Helper\Data $tendopayHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \TendoPay\TendopayPayment\Helper\Data $tendopayHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->tendopayHelper = $tendopayHelper;
    }

    /**
     * @param null $transport
     * @return \Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);

        $helper = $this->tendopayHelper;

        if (!$this->getIsSecureMode()) {
            $info = $this->getInfo();
            $order = $info->getOrder();
            $txnId = $info->getLastTransId();
            if (!$txnId) { // if order doesn't have transaction (for instance: Pending Payment orders)
                $tendopayOrderId = $order->getData('tendopay_order_id');
                $tendopayToken = $order->getData('tendopay_token');
                $tendopayDisposition = $order->getData('tendopay_disposition');
                $tendopayVerificationToken = $order->getData('tendopay_verification_token');
                $tendopayFetchedAt = $order->getData('tendopay_fetched_at');
                $transport->addData(
                    ['Tendopay Order ID' =>
                        $tendopayOrderId ? $tendopayOrderId : __('(none)')]
                );
                $transport->addData(
                    ['Tendopay Order Token' =>
                        $tendopayToken ? $tendopayToken : __('(none)')]
                );
                $transport->addData(
                    ['Tendopay Order Status' =>
                        $tendopayDisposition ? $tendopayDisposition : __('(none)')]
                );
                $transport->addData(
                    ['Tendopay Verification Token' =>
                        $tendopayVerificationToken ? $tendopayVerificationToken : __('(none)')]
                );
                $transport->addData(
                    ['Tendopay Token Fetched At' =>
                        ($tendopayFetchedAt && $tendopayFetchedAt != '0000-00-00 00:00:00') ?
                            $helper->getFormateDate($tendopayFetchedAt, 'M d, Y'):
                            __('(none)')]
                );
            } else { // if order already has transaction
                $transport->addData(['Order ID' => $txnId]);

                $lastTxn = $info->getTransaction($txnId);
                $rawDetails = $lastTxn instanceof \Magento\Sales\Model\Order\Payment\Transaction ?
                    $lastTxn->getAdditionalInformation(
                        \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS
                    ) : false;

                if (is_array($rawDetails)) {
                    if (isset($rawDetails['paymentType'])) {
                        $transport->addData(['Payment Type' => $rawDetails['paymentType']]);
                    }

                    if (isset($rawDetails['status'])) {
                        $transport->addData(['Payment Status' => $rawDetails['status']]);
                    }

                    if (isset($rawDetails['consumerName'])) {
                        $transport->addData(['Consumer Name' => $rawDetails['consumerName']]);
                    }

                    if (isset($rawDetails['consumerEmail'])) {
                        $transport->addData(['Consumer Email' => $rawDetails['consumerEmail']]);
                    }

                    if (isset($rawDetails['consumerTelephone'])) {
                        $transport->addData(['Consumer Tel.' => $rawDetails['consumerTelephone']]);
                    }
                }
            }
        }

        return $transport;
    }
}
