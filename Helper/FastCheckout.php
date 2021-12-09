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

namespace Fast\Checkout\Helper;

use Fast\Checkout\Logger\Logger;
use Fast\Checkout\Model\Config\FastIntegrationConfig;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class FastCheckout extends AbstractHelper
{
    const FAST_BUSINESS_EVENT_CAPTURE = 'BUSINESS_EVENT_TYPE_CLEAR_TO_COLLECT_PAYMENT'; // capture pmt
    const FAST_BUSINESS_EVENT_FULFILLMENT = 'BUSINESS_EVENT_TYPE_FULFILLMENT'; // ship order
    /**
     * @var Config
     */
    protected $saveConfig;
    /**
     * @var ReinitableConfigInterface
     */
    protected $config;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var FastIntegrationConfig
     */
    protected $fastIntegrationConfig;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * FastCheckout constructor.
     * @param Context $context
     * @param Config $saveconfig
     * @param ReinitableConfigInterface $config
     * @param ScopeConfigInterface $scopeConfig
     * @param FastIntegrationConfig $fastIntegrationConfig
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Config $saveconfig,
        ReinitableConfigInterface $config,
        ScopeConfigInterface $scopeConfig,
        FastIntegrationConfig $fastIntegrationConfig,
        Logger $logger
    ) {
        $this->saveConfig = $saveconfig;
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->fastIntegrationConfig = $fastIntegrationConfig;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param string $message
     * @param int $level
     */
    public function log(string $message, int $level = Logger::INFO)
    {
        switch ($level) {
            case Logger::DEBUG:
                if ($this->fastIntegrationConfig->isDebugLoggingEnabled()) {
                    $this->logger->debug($message);
                }
                break;
            case Logger::ERROR:
                $this->logger->error($message);
                break;
            default:
                $this->logger->info($message);
                break;
        }
    }

    /**
     * @return string
     */
    public function guid(): string
    {
        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(16384, 20479),
            random_int(32768, 49151),
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(0, 65535)
        );
    }
}
