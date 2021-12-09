<?php

namespace Fast\Checkout\Plugin\Sales\Model;

use Closure;
use Fast\Checkout\Helper\FastCheckout as FastHelper;
use Fast\Checkout\Model\Config\FastIntegrationConfig as FastConfig;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\CommandInterface as BaseCommandInterface;

/**
 * Class StatusOnCapture
 */
class StatusOnCapture
{
    /**
     * @var FastHelper
     */
    protected $fastHelper;
    /**
     * @var FastConfig
     */
    protected $fastConfig;

    /**
     * StatusOnCapture constructor.
     * @param FastConfig $fastConfig
     * @param FastHelper $fastHelper
     */
    public function __construct(
        FastConfig $fastConfig,
        FastHelper $fastHelper
    ) {
        $this->fastConfig = $fastConfig;
        $this->fastHelper = $fastHelper;
    }

    /**
     * Set pending order status on order place
     * see https://github.com/magento/magento2/issues/5860
     *
     * @param BaseCommandInterface $subject
     * @param Closure $proceed
     * @param OrderPaymentInterface $payment
     * @param $amount
     * @param OrderInterface $order
     * @return mixed
     * @todo Refactor this when another option becomes available
     *
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    public function aroundExecute(
        BaseCommandInterface $subject, //NOSONAR
        Closure $proceed,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    ) {

        $result = $proceed($payment, $amount, $order);

        if ($this->fastConfig->isEnabled()
            && $order->getData('fast_order_id')
            && $order->getState() == Order::STATE_PROCESSING
            && $order->getPayment()->getMethod() === 'fast') {
            $state = Order::STATE_PROCESSING;
            $status = $this->fastConfig->getInvoicedOrderStatus();
            $order->setState($state);
            $order->setStatus($status);
        }

        return $result;
    }
}
