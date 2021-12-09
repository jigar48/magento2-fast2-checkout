<?php

namespace Fast\Checkout\Observer;

use Exception;
use Fast\Checkout\Helper\FastCheckout as FastHelper;
use Fast\Checkout\Model\Config\FastIntegrationConfig as FastConfig;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class InvoiceFastOrderWithCapture
 */
class InvoiceFastOrderWithCapture implements ObserverInterface
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
     * @var OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusRepository;

    /**
     * @var FastHelper
     */
    protected $fastHelper;
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var FastConfig
     */
    protected $fastConfig;
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var Transaction
     */
    protected $transaction;
    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * InvoiceFastOrderWithCapture constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderStatusHistoryRepositoryInterface $orderStatusRepository
     * @param FastHelper $fastHelper
     * @param Order $order
     * @param FastConfig $fastConfig
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        FastHelper $fastHelper,
        Order $order,
        FastConfig $fastConfig,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction
    ) {
        $this->orderRepository = $orderRepository;
        $this->fastConfig = $fastConfig;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->fastHelper = $fastHelper;
        $this->order = $order;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * @param EventObserver $observer
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $fastOrderId = $order->getData('fast_order_id');
        if ($fastOrderId
            && $this->fastConfig->isEnabled()
            && !$this->fastConfig->isAuthCapture()
            && $order->getPayment()->getMethod() === 'fast') {
            try {
                $orderComment = sprintf(
                    __("Invoicing Fast order ID: %s"),
                    $fastOrderId
                );
                $comment = $order->addCommentToStatusHistory($orderComment);
                $this->fastHelper->log($orderComment, 100);
                $this->orderStatusRepository->save($comment);
                if ($order->canInvoice()) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->register();
                    $invoice->capture();
                    $invoice->save();
                    $transactionSave = $this->transaction->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();
                    $this->invoiceSender->send($invoice);
                    //Send Invoice mail to customer
                    $order->addStatusHistoryComment(
                        __('Notified customer about invoice creation #%1.', $invoice->getId())
                    )
                        ->setIsCustomerNotified(true)
                        ->setState('processing')
                        ->setStatus($this->fastConfig->getInvoicedOrderStatus())
                        ->save();
                }
            } catch (Exception $e) {
                $this->fastHelper->log($e->getMessage(), 200);
            }
        }
    }
}
