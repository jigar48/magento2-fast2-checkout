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

namespace Fast\Checkout\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class FastIntegrationConfig
 */
class FastIntegrationConfig
{
    const XPATH_ENABLE = 'fast_integration/fast/enable';
    const XPATH_ENABLE_PRODUCTION = 'fast_integration/fast/mode';
    const XPATH_API_ACCESS_TOKEN = 'fast_integration/fast/api_access_token';
    const XPATH_APP_ID = 'fast_integration/fast/app_id';
    const XPATH_FAST_TEST_API_URI = 'fast_integration/fast/fast_test_api_uri';
    const XPATH_FAST_TEST_JS_URL = 'fast_integration/fast/fast_test_js_url';
    const XPATH_FAST_PROD_API_URI = 'fast_integration/fast/fast_prod_api_uri';
    const XPATH_FAST_PROD_JS_URL = 'fast_integration/fast/fast_prod_js_url';
    const XPATH_ENABLE_DARK_THEME = 'fast_integration/fast/enable_dark_theme';
    const XPATH_ENABLE_DEBUG_LOGGING = 'fast_integration/fast/enable_debug_logging';
    const XPATH_ENABLE_REST_API_LOG = 'fast_integration/rest_api_log/enable_rest_api_log';
    const XPATH_RETRY_FAILURES_COUNT = 'fast_integration/fast/retry_failures_count';
    const XPATH_ENABLE_AUTH_CAPTURE = 'fast_integration/fast/enable_auth_capture';
    const XPATH_ORDER_STATUS_PLANNED_TO_SHIP = 'fast_integration/fast/order_status_planned_to_ship';
    const XPATH_AUTO_INVOICE = 'fast_integration/fast/enable_auto_invoice';


    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * FastIntegrationConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return !$this->isEnabled();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XPATH_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XPATH_ENABLE_PRODUCTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isAuthCapture(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XPATH_ENABLE_AUTH_CAPTURE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getApiAccessToken(): string
    {
        $value = $this->scopeConfig->getValue(static::XPATH_API_ACCESS_TOKEN, ScopeInterface::SCOPE_STORE);
        return isset($value) ? $value : 'not set';
    }

    /**
     * @return string
     */
    public function getInvoicedOrderStatus(): string
    {
        return $this->scopeConfig->getValue(static::XPATH_ORDER_STATUS_PLANNED_TO_SHIP);
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        $value = $this->scopeConfig->getValue(static::XPATH_APP_ID, ScopeInterface::SCOPE_STORE);
        return isset($value) ? $value : 'not set';
    }

    /**
     * @return int
     */
    public function getRetryCount(): int
    {
        return (int)$this->scopeConfig->getValue(static::XPATH_RETRY_FAILURES_COUNT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getFastApiUri(): string
    {
        if ($this->isProduction()) {
            return $this->scopeConfig->getValue(static::XPATH_FAST_PROD_API_URI);
        }
        return $this->scopeConfig->getValue(static::XPATH_FAST_TEST_API_URI);
    }

    /**
     * @return string
     */
    public function getFastJsUrl(): string
    {
        if ($this->isProduction()) {
            return $this->scopeConfig->getValue(static::XPATH_FAST_PROD_JS_URL);
        }
        return $this->scopeConfig->getValue(static::XPATH_FAST_TEST_JS_URL);
    }

    /**
     * @return bool
     */
    public function isDebugLoggingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XPATH_ENABLE_DEBUG_LOGGING);
    }

    /**
     * @return bool
     */
    public function isRestApiLogEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XPATH_ENABLE_REST_API_LOG);
    }

    /**
     * @return bool
     */
    public function useDarkTheme(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XPATH_ENABLE_DARK_THEME, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isEnabledAutoInvoice(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XPATH_AUTO_INVOICE, ScopeInterface::SCOPE_STORE);
    }
}
