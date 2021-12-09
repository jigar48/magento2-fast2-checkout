<?php
/**
 * Fast_Checkout
 *
 * PHP version 7.3
 *
 * @author    Fast <hi@fast.co>
 * @copyright 2021 Copyright Fast AF, Inc., https://www.fast.co/
 * @license   https://opensource.org/licenses/OSL-3.0 OSL-3.0
 * @link      https://www.fast.co/
 */

declare(strict_types=1);

namespace Fast\Checkout\Controller\Adminhtml\Activation;

use Fast\Checkout\Logger\Logger;
use Fast\Checkout\Model\Config\FastIntegrationConfig as FastConfig;
use Fast\Checkout\Service\DoRequest;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Oauth\Exception;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\OauthService;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Send
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Send extends Action
{
    const INTEGRATION_NAME = 'FAST Checkout'; //the name of the integration object
    /**
     * @var IntegrationFactory
     */
    protected $integrationFactory;
    /**
     * @var Token
     */
    protected $oauthToken;
    /**
     * @var OauthService
     */
    protected $oauthService;
    /**
     * @var DoRequest
     */
    protected $curlRequest;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var FastConfig
     */
    protected $fastConfig;
    /**
     * @var ResultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Send constructor.
     * @param Context $context
     * @param IntegrationFactory $integrationFactory
     * @param Token $oauthToken
     * @param OauthService $oauthService
     * @param DoRequest $request
     * @param Logger $logger ,
     * @param FastConfig $fastConfig ,
     * @param ResultJsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        Token $oauthToken,
        OauthService $oauthService,
        DoRequest $request,
        Logger $logger,
        FastConfig $fastConfig,
        ResultJsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->integrationFactory = $integrationFactory;
        $this->oauthToken = $oauthToken;
        $this->oauthService = $oauthService;
        $this->curlRequest = $request;
        $this->logger = $logger;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->fastConfig = $fastConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute()
    {
        $this->logger->debug("in activation execute");
        $fastIntegration = $this->integrationFactory->create()->load(static::INTEGRATION_NAME, 'name')->getData();
        $consumer = $this->oauthService->loadConsumer($fastIntegration["consumer_id"]);
        $token = $this->oauthToken->loadByConsumerIdAndUserType($consumer->getId(), 1)->getToken();
        $callback = $this->fastConfig->getFastApiUri().'auth-integration/magento';
        $payload = [
            'app_id' => $this->fastConfig->getAppId(),
            'merchant_api_url' => $this->storeManagerInterface->getStore()->getBaseUrl(),
            'access_token' => $token,
            'site_id' => $this->storeManagerInterface->getStore()->getId()
        ];
        // Send key and display results
        $valid = 0;
        $responsePrefix = 'sending activation ';
        $responseText = $responsePrefix . 'failure';
        if ($this->curlRequest->execute($callback, $payload)) {
            $valid = 1;
            $responseText = $responsePrefix . 'success';
        }
        $this->logger->debug('response valid: ' . $valid . ' ' . $responseText);
        $resultJson = $this->resultJsonFactory->create();
        $data = [
            'valid' => $valid,
            'responseText' => $responseText
        ];
        $resultJson->setData($data);

        return $resultJson;
    }
}
