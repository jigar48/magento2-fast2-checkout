<?php

namespace Fast\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\ShipmentItemRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BeforeShipment
 */
class BeforeShipment implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;
    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;
    /**
     * @var ShipmentItemRepositoryInterface
     */
    protected $shipmentItemRepository;

    /**
     * BeforeShipment constructor.
     * @param LoggerInterface $logger
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ShipmentItemRepositoryInterface $shipmentItemRepository
     */
    public function __construct(
        LoggerInterface $logger,
        OrderItemRepositoryInterface $orderItemRepository,
        ShipmentItemRepositoryInterface $shipmentItemRepository
    ) {
        $this->_logger = $logger;
        $this->orderItemRepository = $orderItemRepository;
        $this->shipmentItemRepository = $shipmentItemRepository;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        foreach ($shipment->getItemsCollection() as $item) {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            $fastOrderItemUuid = $orderItem->getData('fast_order_item_uuid');
            $item->setData('fast_order_item_uuid', $fastOrderItemUuid);
        }
        return $this;
    }
}
