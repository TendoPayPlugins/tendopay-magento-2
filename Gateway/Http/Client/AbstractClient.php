<?php

namespace TendoPay\TendopayPayment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use TendoPay\TendopayPayment\Client\ClientFactoryInterface;

/**
 * Class AbstractClient
 * Base class for gateway client classes
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ClientFactoryInterface
     */
    protected $_clientFactory;

    /**
     * AbstractClient constructor.
     * @param ClientFactoryInterface $clientFactory
     * @param Logger $logger
     */
    public function __construct(
        ClientFactoryInterface $clientFactory,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->_clientFactory = $clientFactory;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {

        $data = $transferObject->getBody();

        $log = [
            'request' => $transferObject->getBody(),
            'client' => static::class
        ];

        $response = [];

        try {
            $response = $this->process($data);
        } catch (\Exception $e) {
            $message = $e->getMessage() ? $e->getMessage() : "Something went wrong during Gateway request.";
            $log['error'] = $message;
            $this->logger->debug($log);
        }

        return $response;
    }

    /**
     * Process http request
     *
     * @param array $data
     */
    abstract protected function process(array $data);
}
