<?php


namespace TendoPay\TendopayPayment\Client;


use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use TendoPay\TendopayPayment\Helper\Data;

class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var string
     */
    private $instanceName;

    /**
     * ClientFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $config
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $config,
        $instanceName = '\\TendoPay\\TendopayPayment\\Client\\Client'
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->instanceName = $instanceName;
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        $config = [
            Data::CLIENT_ID_KEY => $this->config->getValue('api_client_id'),
            Data::CLIENT_SECRET_KEY => $this->config->getValue('api_client_secret'),
            Data::TENDOPAY_SANDBOX_ENABLED => $this->config->getValue('api_mode')
        ];
        return $this->objectManager->create($this->instanceName, ['config' => $config]);
    }
}
