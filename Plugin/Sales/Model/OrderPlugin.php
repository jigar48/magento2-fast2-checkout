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

namespace Fast\Checkout\Plugin\Sales\Model;

use Fast\Checkout\Helper\FastCheckout as FastHelper;
use Fast\Checkout\Logger\Logger;
use Fast\Checkout\Model\Config\FastIntegrationConfig as FastConfig;
use Fast\Checkout\Service\DoRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Sales\Model\Order;

/**
 * Class OrderPlugin
 */
class OrderPlugin
{
    const FAST_ORDER_CANCEL_ENDPOINT = 'external/orders/:order_id.value';
    /**
     * @var FastHelper
     */
    protected $fastHelper;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var FastConfig
     */
    protected $fastConfig;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var DoRequest
     */
    protected $doRequest;

    /**
     * OrderPlugin constructor.
     * @param FastConfig $fastConfig
     * @param Logger $logger
     * @param FastHelper $fastHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Order $order
     * @param DoRequest $doRequest
     */
    public function __construct(
        FastConfig $fastConfig,
        Logger $logger,
        FastHelper $fastHelper,
        ScopeConfigInterface $scopeConfig,
        Order $order,
        DoRequest $doRequest
    ) {
        $this->fastConfig = $fastConfig;
        $this->fastHelper = $fastHelper;
        $this->logger = $logger;
        $this->scopeConfigInterface = $scopeConfig;
        $this->order = $order;
        $this->doRequest = $doRequest;
    }

    /**
     * @param Order $order
     * @return Order
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function beforeCancel(Order $order) //phpcs:ignore
    {
        if ($this->fastConfig->isEnabled()
            && $order->getFastOrderId()
            && $order->getPayment()->getMethod() === 'fast') {
            $this->logger->debug("in beforeCancel order");
            $callback = $this->fastConfig->getFastApiUri() . static::FAST_ORDER_CANCEL_ENDPOINT;
            $callback = str_replace(':order_id.value', $order->getFastOrderId(), $callback);
            $payload = [
                'order_id' => ["value" => (string)$order->getFastOrderId()],
                'reason' => "CANCEL_REASON_CODE_MERCHANT_INITIATED",
                'notes' => "magento order id " . $order->getIncrementId()
            ];
            // Send key and display results
            $response = $this->doRequest->execute($callback, $payload);

            if ($this->isErrorInRefund($response)) {
                $this->logger->debug("order cancelled" . $order->getIncrementId());
            } else {
                $this->logger->info("order not cancelled " . $order->getIncrementId());
                throw new ClientException(
                    __('The order could not be canceled. Please check the fast-checkout.log')
                );
            }
            $this->logger->debug("out of beforeCancel order");
        }
        return $order;
    }

    /**
     * @param $response
     * @return bool
     */
    protected function isErrorInRefund($response)
    {
        $status = (int)$response->getStatus();
        return $status == 200 ? true : false;

    }
}
