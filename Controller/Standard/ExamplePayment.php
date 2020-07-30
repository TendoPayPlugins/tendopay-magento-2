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

namespace TendoPay\TendopayPayment\Controller\Standard;

use Magento\Framework\App\Action\Context;

class ExamplePayment extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \TendoPay\TendopayPayment\Helper\InstallmentsRetriever
     */
    public $installmentsRetriever;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Magento\Directory\Model\PriceCurrencyFactory
     */
    public $priceCurrency;

    /**
     * ExamplePayment constructor.
     * @param Context $context
     * @param \TendoPay\TendopayPayment\Helper\InstallmentsRetriever $installmentsRetriever
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Directory\Model\PriceCurrencyFactory $priceCurrency
     */
    public function __construct(
        Context $context,
        \TendoPay\TendopayPayment\Helper\InstallmentsRetriever $installmentsRetriever,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Directory\Model\PriceCurrencyFactory $priceCurrency
    ) {
        $this->installmentsRetriever = $installmentsRetriever;
        $this->priceCurrency = $priceCurrency;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute()
    {
        $returnJson = [];
        $price = $this->getRequest()->getParam("price");
        $getExamplePayment = $this->installmentsRetriever->getExamplePayment($price);
        if ($getExamplePayment!="") {
            $getExamplePayment = $this->priceCurrency->create()->convertAndFormat($getExamplePayment, false, 0);
            $returnJson['data'] = [
                'response' => __('Or as low as <strong>%1/installment</strong> with', $getExamplePayment)
            ];
            $returnJson['success'] = true;
        }

        return $this->resultJsonFactory->create()->setData($returnJson);
    }
}
