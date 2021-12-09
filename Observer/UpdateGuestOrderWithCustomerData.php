<?php

namespace Fast\Checkout\Observer;

use Exception;
use Fast\Checkout\Helper\FastCheckout as FastHelper;
use Fast\Checkout\Logger\Logger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class UpdateGuestOrderWithCustomerData
 */
class UpdateGuestOrderWithCustomerData implements ObserverInterface
{
    /**
     * @var PurchasedFactory
     */
    protected $purchasedFactory;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusRepository;

    /**
     * @var FastHelper
     */
    protected $fastHelper;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * UpdateGuestOrderWithCustomerData constructor.
     * @param PurchasedFactory $purchasedFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderStatusHistoryRepositoryInterface $orderStatusRepository
     * @param FastHelper $fastHelper
     * @param Order $order
     * @param Logger $logger
     * @param OrderSender $orderSender
     */
    public function __construct(
        PurchasedFactory $purchasedFactory,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        FastHelper $fastHelper,
        Order $order,
        Logger $logger,
        OrderSender $orderSender
    ) {
        $this->purchasedFactory = $purchasedFactory;
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->fastHelper = $fastHelper;
        $this->logger = $logger;
        $this->order = $order;
        $this->orderSender = $orderSender;
    }

    /**
     * @param EventObserver $observer
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $incrementId = $order->getIncrementId();
        $orderId = $order->getEntityId();
        $fastOrderId = $order->getData('fast_order_id');
        if ($fastOrderId) {
            try {
                $orderComment = sprintf(
                    __("Fast order ID: %s"),
                    $fastOrderId
                );
                $comment = $order->addCommentToStatusHistory($orderComment);
                $this->orderStatusRepository->save($comment);
                $customer = $this->customerRepository->get($order->getCustomerEmail());
                $customerId = $customer->getId();
                $customerOrder = $order;
                if ($order->getCustomerIsGuest() && $customerId) {
                    $customerOrder = $this->orderRepository->get($orderId);
                    $this->fastHelper->log(
                        "current order id converting from guest to customer "
                        . $orderId . ' ' . $customerId,
                        200
                    );
                    $customerOrder->setCustomerIsGuest(0);
                    $customerOrder->setCustomerId($customerId);
                    $customerOrder->setCustomerGroupId($customer->getGroupId());
                    $this->orderRepository->save($customerOrder);
                    $purchased = $this->purchasedFactory->create()->load(
                        $incrementId,
                        'order_increment_id'
                    );
                    if ($purchased->getId()) {
                        $purchased->setCustomerId($customer->getId());
                        $purchased->save();
                    }
                }
                $this->orderSender->send($customerOrder, true);
            } catch (Exception $e) {
                $this->logger->info($e->getMessage());
            }
        }
    }
}
