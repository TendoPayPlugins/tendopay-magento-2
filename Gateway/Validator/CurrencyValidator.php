<?php

namespace TendoPay\TendopayPayment\Gateway\Validator;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CurrencyValidator
 * Validates allowable currencies for Tendo Pay
 */
class CurrencyValidator extends AbstractValidator
{

    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * CurrencyValidator constructor.
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param ConfigInterface $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        parent::__construct($resultFactory);
        $this->_storeManager = $storeManager;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     * @throws NoSuchEntityException
     */
    public function validate(array $validationSubject)
    {
        $allowedCurrency = $this->config->getValue('currency');
        $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

        if ($allowedCurrency == $currentCurrency) {
            return $this->createResult(
                true,
                ['status' => 200]
            );
        }

        return $this->createResult(
            false,
            [__('The currency selected is not supported by Tendo Pay.')]
        );
    }
}
