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

namespace Fast\Checkout\Observer;

use Exception;
use Fast\Checkout\Helper\FastCheckout as FastHelper;
use Fast\Checkout\Logger\Logger;
use Fast\Checkout\Model\Config\FastIntegrationConfig as FastConfig;
use Fast\Checkout\Service\DoRequest;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentItemRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Psr\Log\LoggerInterface;

/**
 * Class NotifyFastOnShipment
 * notify fast when an order has been shipped
 */
class NotifyFastOnShipment implements ObserverInterface
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
     * @var Transaction
     */
    protected $transaction;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ScopeConfig
     */
    protected $scopeConfig;
    /**
     * @var DoRequest
     */
    protected $doRequest;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;
    /**
     * @var ShipmentItemRepositoryInterface
     */
    protected $shipItemRepository;

    /**
     * NotifyFastOnShipment constructor.
     * @param Transaction $transaction
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param ScopeConfig $scopeConfig
     * @param FastConfig $fastConfig
     * @param FastHelper $fastHelper
     * @param DoRequest $doRequest
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ShipmentItemRepositoryInterface $shipItemRepository
     */
    public function __construct(
        Transaction $transaction,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        ScopeConfig $scopeConfig,
        FastConfig $fastConfig,
        FastHelper $fastHelper,
        DoRequest $doRequest,
        OrderItemRepositoryInterface $orderItemRepository,
        ShipmentItemRepositoryInterface $shipItemRepository
    ) {
        $this->transaction = $transaction;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->fastConfig = $fastConfig;
        $this->fastHelper = $fastHelper;
        $this->doRequest = $doRequest;
        $this->orderItemRepository = $orderItemRepository;
        $this->shipItemRepository = $shipItemRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->fastConfig->isEnabled()) {
            try {
                /** @var Shipment $shipment */
                $shipment = $observer->getEvent()->getShipment();
                /** @var ShipmentItemInterface $item */
                foreach ($shipment->getItemsCollection() as $item) {
                    $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
                    $fastOrderItemUuid = $orderItem->getData('fast_order_item_uuid');
                    $item->setData('fast_order_item_uuid', $fastOrderItemUuid);
                    $this->shipItemRepository->save($item);
                    /** @var Order $order */
                    $order = $observer->getEvent()->getShipment()->getOrder();
                    if ($order->getFastOrderId()) {
                        $this->fastHelper->log(
                            "sending shipment info for order: " . $order->getIncrementId(),
                            Logger::DEBUG
                        );

                        $json = $this->getPayloadJson($shipment, $order);
                        $uri = $this->fastConfig->getFastApiUri() . 'external/orders/' .
                            $order->getFastOrderId() . '/business_event';
                        $this->fastHelper->log(
                            json_encode($json),
                            Logger::DEBUG
                        );
                        $this->doRequest->execute($uri, $json, 'POST');
                    }
                }
            } catch (Exception $e) {
                $this->fastHelper->log("sending shipment info: FAILURE");
                $this->fastHelper->log($e->getMessage());
                return;
            }
            $this->fastHelper->log("sending shipment info: SUCCESS", Logger::DEBUG);
        }
    }

    /**
     * @param $shipment
     * @param $order
     * @return array
     */
    protected function getPayloadJson(Shipment $shipment, Order $order)
    {
        $orderLines = [];
        /** @var Item $item */
        foreach ($shipment->getItems() as $item) {
            if ($item->getData('fast_order_item_uuid') !== null) {
                $orderLines[] = [
                    'id' => ['value' => $item->getData('fast_order_item_uuid')],
                    'quantity' => (string)$item->getQty()
                ];
            }
        }
        /** @var ShipmentTrackInterface[] $tracks */
        $tracks = $shipment->getTracks();
        $carrier = 'none';
        $trackingNumber = 'none';
        /** @var ShipmentTrackInterface $track */
        foreach ($tracks as $track) {
            $carrier = $track->getCarrierCode();
            $trackingNumber = $track->getTrackNumber();
        }
        return [
            'order_id' => ['value' => $order->getFastOrderId()],
            'event_type' => 'BUSINESS_EVENT_TYPE_FULFILLMENT',
            'fulfillment' => [
                'order_id' => ['value' => $order->getFastOrderId()],
                'shipment' => [
                    'tracking_number' => $trackingNumber,
                    'carrier' => $carrier,
                    'estimated_delivery_date' => '',
                    'order_lines' => $orderLines
                ],
                'status' => 'ORDER_STATUS_FULFILLED'
            ]
        ];
    }
}
