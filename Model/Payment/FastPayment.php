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

namespace Fast\Checkout\Model\Payment;

use Fast\Checkout\Api\Data\DoRequestLogInterface;
use Fast\Checkout\Helper\FastCheckout as FastCheckoutHelper;
use Fast\Checkout\Model\Config\FastIntegrationConfig as FastConfig;
use Fast\Checkout\Service\DoRequest;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Exception;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger as PaymentLogger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Exception\CouldNotRefundException;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Payment\Transaction;
use Monolog\Logger;

/**
 * FastPayment Initialization
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class FastPayment extends AbstractMethod
{
    const FAST_REFUND_ENDPOINT = 'external/orders/:order_id.value/refund';
    /**
     * @var string
     */
    protected $_code = "fast";
    /**
     * @var bool
     */
    protected $_isOffline = false;
    /**
     * @var
     */
    protected $_custompayments;
    /**
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * @var bool
     */
    protected $_canCapture = true;
    /**
     * @var bool
     */
    protected $_canCapturePartial = true;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var array
     */
    protected $_supportedCurrencyCodes = ['USD', 'GBP', 'EUR'];

    /**
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * @var FastCheckoutHelper
     */
    protected $fastCheckoutHelper;

    /**
     * @var DoRequest
     */
    protected $doRequest;

    /**
     * @var FastConfig
     */
    protected $fastConfig;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * FastPayment constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param PaymentHelper $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param PaymentLogger $logger
     * @param FastCheckoutHelper $fastCheckoutHelper
     * @param DoRequest $doRequest
     * @param FastConfig $fastConfig
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        PaymentHelper $paymentData,
        ScopeConfigInterface $scopeConfig,
        PaymentLogger $logger,
        FastCheckoutHelper $fastCheckoutHelper,
        DoRequest $doRequest,
        FastConfig $fastConfig,
        OrderItemRepositoryInterface $orderItemRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->fastCheckoutHelper = $fastCheckoutHelper;
        $this->doRequest = $doRequest;
        $this->fastConfig = $fastConfig;
        $this->orderItemRepository = $orderItemRepository;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            null
        );
    }

    /**
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this|AbstractMethod
     * @throws Exception
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        if ($order->getFastOrderId()) {
            try {
                $charge = [
                    'amount' => $amount * 100,
                    'currency' => strtolower($order->getBaseCurrencyCode()),
                    'description' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail())
                ];
                //get fast tx id here or generate unique hash
                $fastTxnId = $this->fastCheckoutHelper->guid();
                $this->sendCapture($order);
                $payment->setAdditionalInformation(json_encode($charge));
                $payment->setTransactionId($fastTxnId)->setParentTransactionId($fastTxnId)->setIsTransactionClosed(0);
                $this->fastCheckoutHelper->log(json_encode($charge) . ' ' . $fastTxnId, Logger::DEBUG);
                return $this;
            } catch (\Exception $e) {
                $this->fastCheckoutHelper->log($e->getMessage());
                throw new CouldNotSaveException(__('Payment capturing error.'));
            }
        }
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this|AbstractMethod
     * @throws Exception
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $transactionId = $payment->getParentTransactionId();
        $this->fastCheckoutHelper->log('transaction id in refund: ' . $transactionId, Logger::DEBUG);
        $order = $payment->getOrder();
        if ($order->getFastOrderId()) {
            try {
                $this->fastCheckoutHelper->log("in before Refund", Logger::DEBUG);
                // send fast the refund request
                $this->sendRefund($order, $payment, $amount);
            } catch (\Exception $e) {
                $this->fastCheckoutHelper->log($e->getMessage());
                throw new CouldNotSaveException(__('Payment refunding error.'));
            }

            $payment
                ->setTransactionId($transactionId . '-' . Transaction::TYPE_REFUND)
                ->setParentTransactionId($transactionId)
                ->setIsTransactionClosed(1)
                ->setShouldCloseParentTransaction(1);
        } else {
            $this->fastCheckoutHelper->log('no fast order id');
            throw new NotFoundException(__('No Fast Order ID.'));
        }

        return $this;
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    public function isAvailable(CartInterface $quote = null)
    {
        return true;
    }

    /**
     * @param $order
     * @return DoRequestLogInterface
     * @throws Exception
     * @throws LocalizedException
     */
    protected function sendCapture($order)
    {
        $response = true;
        if ($this->fastConfig->isAuthCapture()) {
            $callback = $this->fastConfig->getFastApiUri() . 'external/orders/' .
                $order->getFastOrderId() . '/business_event';
            $payload = [
                'order_id' => ['value' => $order->getFastOrderId()],
                "event_type" => FastCheckoutHelper::FAST_BUSINESS_EVENT_CAPTURE,

            ];
            $response = $this->doRequest->execute($callback, $payload);
            if ($response->getStatus() == 200) {
                $this->fastCheckoutHelper->log("order captured " . $order->getIncrementId(), 100);
            } else {
                $this->fastCheckoutHelper->log("order not captured " . $order->getIncrementId(), 100);
                $this->fastCheckoutHelper->log(
                    "order not captured " . json_encode($response->getResponseContent()->message),
                    200
                );
                throw new CouldNotSaveException(
                    __('Could not capture fast payment, see error log for details')
                );
            }
        }
        return $response;
    }

    /**
     * @param $order
     * @param $payment
     * @param $amount
     * @return bool
     * @throws LocalizedException
     */
    protected function sendRefund($order, $payment, $amount)
    {
        if ($amount <= 0) {
            throw new LocalizedException(__('Invalid amount for refund.'));
        }

        if ($amount > $order->getBaseGrandTotal()) {
            throw new LocalizedException(__('Invalid amount for refund.'));
        }
        $transactionId = $payment->getParentTransactionId();
        if ($transactionId) {
            $callback = $this->fastConfig->getFastApiUri() . static::FAST_REFUND_ENDPOINT;
            $callback = str_replace(':order_id.value', $order->getFastOrderId(), $callback);
            $payload = [
                'order_id' => ["value" => (string)$order->getFastOrderId()],
                'external_refund_id' => $transactionId,
                'reason' => "REFUND_REASON_CODE_UNSPECIFIED",
                'note' => "magento order id " . $order->getIncrementId(),
                'method' => "REFUND_METHOD_ORIGINAL_METHOD",
                'amount' => (string)number_format($payment->getCreditMemo()->getBaseGrandTotal(), 2, '.', ''),
                'tax_amount' => (string)number_format($payment->getCreditMemo()->getTaxAmount(), 2, '.', ''),
                'shipping_amount' => (string)number_format($payment->getCreditMemo()->getShippingInclTax(), 2, '.', ''),
                'order_lines' => $this->encodeItems($payment->getCreditMemo())
            ];
            // Send key and display results
            $response = $this->doRequest->execute($callback, $payload);
            if ($response->getStatus() == 200) {
                $this->fastCheckoutHelper->log("order refunded" . $order->getIncrementId(), Logger::DEBUG);
            } else {
                $this->fastCheckoutHelper->log("order not refunded " . $order->getIncrementId());
                throw new CouldNotRefundException(
                    __('Could not save a Creditmemo, see error log for details')
                );
            }
        }
        return true;
    }

    /**
     * @param Creditmemo $creditmemo
     * @return array
     */
    private function encodeItems(Creditmemo $creditmemo)
    {
        /** @var @var \Magento\Sales\Model\Order\Creditmemo\Item[] $items */
        $items = $creditmemo->getItems();
        $lines = [];
        /** @var @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($items as $item) {
            $orderItemId = $item->getOrderItemId();
            /** @var OrderItemInterface $orderItem */
            $orderItem = $this->orderItemRepository->get($orderItemId);
            $value = $orderItem->getData('fast_order_item_uuid');
            if ($orderItem->getParentItemId()) {
                $parent = $orderItem->getParentItem();
                $value = $parent->getData('fast_order_item_uuid');
            }
            if ($value && $orderItem->getProductType() !== 'configurable') {
                $lines[] = [
                    'line' =>
                        [
                            'id' => [
                                'value' => $value
                            ],
                            'quantity' => $item->getQty()
                        ],
                    'reason' => "REFUND_REASON_CODE_UNSPECIFIED"
                ];
            }
        }
        return array_values($lines);
    }
}
