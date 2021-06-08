<?php

namespace TendoPay\TendopayPayment\Model\Service;

use Exception;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use TendoPay\SDK\Models\VerifyTransactionResponse;
use TendoPay\TendopayPayment\Helper\Data;

class Tendo
{
    /**
     * @var Quote
     */
    private $_quote;
    /**
     * @var Session
     */
    private $_customerSession;
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $_checkoutData;
    /**
     * @var CartManagementInterface
     */
    private $_quoteManagement;
    private $_order;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        CartManagementInterface $quoteManagement,
        CartRepositoryInterface $quoteRepository,
        $params = []
    ) {
        if (isset($params['quote']) && $params['quote'] instanceof Quote) {
            $this->_quote = $params['quote'];
        } else {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception('Quote instance is required.');
        }
        $this->_customerSession = $customerSession;
        $this->_checkoutData = $checkoutData;
        $this->_quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
    }

    public function placeOrder()
    {
        if ($this->getCheckoutMethod() == Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }

        $this->_quote->collectTotals();
        $order = $this->_quoteManagement->submit($this->_quote);

        if (!$order) {
            return;
        }

        $this->_order = $order;
    }

    protected function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return Onepage::METHOD_CUSTOMER;
        }
        if (!$this->_quote->getCheckoutMethod()) {
            if ($this->_checkoutData->isAllowedGuestCheckout($this->_quote)) {
                $this->_quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            } else {
                $this->_quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
            }
        }
        return $this->_quote->getCheckoutMethod();
    }

    /**
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    protected function prepareGuestQuote()
    {
        $quote = $this->_quote;
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @param VerifyTransactionResponse $data
     * @throws LocalizedException
     */
    public function prepareCallbackData(VerifyTransactionResponse $data)
    {
        $quote = $this->_quote;
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation(Data::PAYMENT_INFO_TP_TRANSACTION_ID, $data->getTransactionNumber());
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }
}
