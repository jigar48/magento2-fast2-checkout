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
use Fast\Checkout\Helper\FastCheckout as FastCheckoutHelper;
use Fast\Checkout\Model\Config\FastIntegrationConfig as FastConfig;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class CreateInvoiceOnShipment
 * create an invoice when an order is shipped
 */
class CreateInvoiceOnShipment implements ObserverInterface
{
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
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var FastCheckoutHelper\
     */
    protected $fastCheckoutHelper;
    /**
     * @var FastConfig
     */
    protected $fastConfig;
    /**
     * @var HistoryFactory
     */
    private $orderHistoryFactory;

    /**
     * CreateInvoiceOnShipment constructor.
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceSender $invoiceSender
     * @param FastCheckoutHelper $fastCheckoutHelper
     * @param FastConfig $fastConfig
     */
    public function __construct(
        InvoiceService $invoiceService,
        Transaction $transaction,
        OrderRepositoryInterface $orderRepository,
        InvoiceSender $invoiceSender,
        FastCheckoutHelper $fastCheckoutHelper,
        FastConfig $fastConfig,
        HistoryFactory $orderHistoryFactory
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->orderRepository = $orderRepository;
        $this->fastCheckoutHelper = $fastCheckoutHelper;
        $this->fastConfig = $fastConfig;
        $this->orderHistoryFactory = $orderHistoryFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getShipment()->getOrder();
        if ($this->fastConfig->isEnabled()
            && $this->fastConfig->isEnabledAutoInvoice()
            && $order->getData('fast_order_id')
            && $order->getPayment()->getMethod() === 'fast') {
            try {
                $this->fastCheckoutHelper->log("generating invoice for order: " . $order->getIncrementId() . " fast order id " . $order->getData('fast_order_id'));

                if (!$order->canInvoice()) {
                    $this->fastCheckoutHelper->log("order cannot be invoiced. exiting");
                    return;
                }
                if ($order->getState() === 'new') {
                    $this->fastCheckoutHelper->log("order in invalid state for invoicing. exiting");
                    return;
                }

                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->save();
                $transactionSave = $this->transaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();
                $this->invoiceSender->send($invoice);//Send Invoice mail to customer

                $history = $this->orderHistoryFactory->create()
                    ->setEntityName(Order::ENTITY)
                    ->setComment(__('Notified customer about invoice creation #%1.', $invoice->getId()))
                    ->setIsCustomerNotified(true);

                $order->addStatusHistory($history);
                $this->orderRepository->save($order);

            } catch (Exception $e) {
                $this->fastCheckoutHelper->log("invoice generation: FAILURE");
                $this->fastCheckoutHelper->log($e->getMessage());
            }
            $this->fastCheckoutHelper->log("invoice generation: SUCCESS");
        }
    }
}
