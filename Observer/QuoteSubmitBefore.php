<?php

namespace Fast\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class QuoteSubmitBefore implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        // Get Order Object
        /* @var $order Order */
        $order = $event->getOrder();
        // Get Quote Object
        /** @var $quote Quote $quote */
        $quote = $event->getQuote();

        $order->setData('fast_order_id', $quote->getData('fast_order_id'));
    }
}
