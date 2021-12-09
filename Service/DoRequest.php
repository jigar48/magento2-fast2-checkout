<?php
/**
 * Fast_Checkout
 *
 * PHP version 7.3
 *
 * @package   Fast_Checkout
 * @author    Fast <hi@fast.co>
 * @copyright 2021 Copyright Fast AF, Inc., https://www.fast.co/
 * @license   https://opensource.org/licenses/OSL-3.0 OSL-3.0
 * @link      https://www.fast.co/
 */

declare(strict_types=1);

namespace Fast\Checkout\Service;

use Exception;
use Fast\Checkout\Api\Data\DoRequestLogInterface;
use Fast\Checkout\Api\Data\DoRequestLogInterfaceFactory;
use Fast\Checkout\Api\DoRequestLogRepositoryInterfaceFactory;
use Fast\Checkout\Helper\FastCheckout;
use Fast\Checkout\Logger\Logger;
use Fast\Checkout\Model\Config\FastIntegrationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DoRequest sends an API request to Fast and logs response in database
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class DoRequest
{
    const AGENT = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
    /**
     * @var DoRequestLogInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private $doRequestLogInterfaceFactory;
    /**
     * @var DoRequestLogRepositoryInterfaceFactory
     */
    private $doRequestLogRepositoryInterfaceFactory;
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var FastIntegrationConfig
     */
    private $fastIntegrationConfig;

    private $logger;

    private $fastCheckoutHelper;

    private $storeMananger;

    /**
     * DoRequest constructor.
     * @param FastIntegrationConfig $fastIntegrationConfig
     * @param DoRequestLogInterfaceFactory $doRequestLogInterfaceFactory
     * @param DoRequestLogRepositoryInterfaceFactory $doRequestLogRepositoryInterfaceFactory
     * @param Curl $curl
     * @param Logger $logger
     * @param FastCheckout $fastCheckoutHelper
     * @param StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        FastIntegrationConfig $fastIntegrationConfig,
        DoRequestLogInterfaceFactory $doRequestLogInterfaceFactory,
        DoRequestLogRepositoryInterfaceFactory $doRequestLogRepositoryInterfaceFactory,
        Curl $curl,
        Logger $logger,
        StoreManagerInterface $storeManager,
        FastCheckout $fastCheckoutHelper
    ) {
        $this->doRequestLogInterfaceFactory = $doRequestLogInterfaceFactory;
        $this->doRequestLogRepositoryInterfaceFactory = $doRequestLogRepositoryInterfaceFactory;
        $this->curl = $curl;
        $this->fastIntegrationConfig = $fastIntegrationConfig;
        $this->logger = $logger;
        $this->fastCheckoutHelper = $fastCheckoutHelper;
        $this->storeMananger = $storeManager;
    }

    /**
     * @param string $requestUri
     * @param array $body
     * @param string $requestMethod
     * @param int $doRequestLogId
     * @return DoRequestLogInterface
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function execute(
        string $requestUri,
        array $body = [],
        string $requestMethod = 'POST',
        int $doRequestLogId = 0
    ): DoRequestLogInterface {
        $this->fastCheckoutHelper->log("in DoRequest request method: " . $requestMethod, Logger::DEBUG);

        $newBody = ['body' => $body];
        $requestBody = json_encode($body);
        $this->fastCheckoutHelper->log("requestBody: " . $requestBody, Logger::DEBUG);
        $requestId = $this->fastCheckoutHelper->guid();

        $headers = [
            "Content-Type" => "application/json",
            "User-Agent" => static::AGENT,
            "X-Fast-App" => $this->fastIntegrationConfig->getAppId(),
            "X-Fast-App-Auth" => $this->fastIntegrationConfig->getApiAccessToken(),
            "X-Fast-Record-Id" => $requestId,
            "Magento-Store-Id" => $this->storeMananger->getStore()->getId(),
            'Expect:' => ''
        ];
        $this->curl->setHeaders($headers);
        switch ($requestMethod) {
            case 'POST':
                $this->curl->post($requestUri, $requestBody);
                break;
            case 'GET':
                $this->curl->get($requestUri);
                break;
            default:
                $this->logger->log($requestMethod . " is not implemented yet.", Logger::DEBUG);
                throw new LocalizedException(__('This %1 request method is not implemented yet.', $requestMethod));
        }
        $result = $this->curl->getBody();
        $status = $this->curl->getStatus();
        $retryStatus = $this->checkRetryStatus($status);

        $this->fastCheckoutHelper->log("response status: " . $status, Logger::DEBUG);
        $this->fastCheckoutHelper->log("response_content: " . $result, Logger::DEBUG);
        try {
            $repository = $this->doRequestLogRepositoryInterfaceFactory->create();
            $this->fastCheckoutHelper->log('after create', Logger::DEBUG);
            if ($doRequestLogId > 0) {
                $model = $repository->get($doRequestLogId);
                $model->setAttempts($model->getAttempts() + 1);
                $model->setResponseContent($result);
                $model->setRetryRequired($this->checkRetryStatus($status));
                $model->setStatus($status);
            } else {
                $model = $this->doRequestLogInterfaceFactory->create();
                $model->setAttempts(0);
                $model->setRequestId($requestId);
                $model->setBody(json_encode($newBody));
                $model->setPriority(0);
                $model->setRequestMethod($requestMethod);
                $model->setResponseContent($result);
                $model->setRetryRequired($this->checkRetryStatus($status));
                $model->setStatus($status);
                $model->setUriEndpoint($requestUri);
            }
            if ($retryStatus > 0) {
                $this->fastCheckoutHelper->log(
                    "do request retry required - status is NOT 200 " . $status . ' ' . $result,
                    Logger::DEBUG
                );
            }
        } catch (Exception $e) {
            $this->fastCheckoutHelper->log($e->getMessage(), Logger::DEBUG);
        }
        $this->fastCheckoutHelper->log('dorequest execute about to save log', Logger::DEBUG);
        $returnResult = false;
        try {
            $returnResult = $repository->save($model);
            $this->fastCheckoutHelper->log('saved row ', Logger::DEBUG);

        } catch (Exception $e) {
            $this->fastCheckoutHelper->log($e->getMessage(), Logger::DEBUG);
        }
        $this->fastCheckoutHelper->log('rreturning', Logger::DEBUG);

        return $returnResult;
    }

    /**
     * @param $result
     * @param $status
     * @return int
     */
    protected function checkRetryStatus($status)
    {
        if ($status != 200) {
            return 1;
        }
        return 0;
    }
}
