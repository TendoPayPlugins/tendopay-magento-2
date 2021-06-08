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

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use TendoPay\SDK\Models\VerifyTransactionRequest;
use TendoPay\SDK\V2\ConstantsV2;
use TendoPay\SDK\V2\TendoPayClient;
use TendoPay\TendopayPayment\Controller\TendopayAbstract;

/**
 * Class Success
 * @package TendoPay\TendopayPayment\Controller\Standard
 */
class Success extends TendopayAbstract
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $postedData = $this->getRequest()->getParams();

        $client = $this->_clientFactory->create();

        if (TendoPayClient::isCallbackRequest($postedData)) {
            $transaction = $client->verifyTransaction(new VerifyTransactionRequest($postedData));

            if (!$transaction->isVerified()) {
                throw new LocalizedException(__('Invalid signature for the verification'));
            }

            if ($transaction->getStatus() == ConstantsV2::STATUS_SUCCESS) {
                $this->_initService();
                $this->_service->prepareCallbackData($transaction);
                $this->_success();
                $this->_redirect('checkout/onepage/success');
            } elseif ($transaction->getStatus() == ConstantsV2::STATUS_FAILURE) {
                $this->messageManager->addWarningMessage(__($transaction->getMessage()));

                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('checkout/cart');
            } elseif ($transaction->getStatus() === ConstantsV2::STATUS_CANCELED) {
                if ($this->_config->getValue('debug')) {
                    $this->logger->debug(json_encode($transaction->toArray()));

                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('checkout/cart');
                }
            }
        }
    }

    protected function _success()
    {
        $this->_initService();
        $this->_service->placeOrder();

        $quoteId = $this->getQuote()->getId();
        $this->getCheckoutSession()->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

        $order = $this->_service->getOrder();

        if ($order) {
            $this->getCheckoutSession()->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId());
        }
    }

}
