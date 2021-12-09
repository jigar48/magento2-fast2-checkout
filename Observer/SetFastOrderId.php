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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class SetFastOrderId
 */
class SetFastOrderId implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this;
     * @throws Exception
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

        if ($quote->getFastOrderId()) {
            $order->setFastOrderId($quote->getFastOrderId());
        }
        return $this;
    }
}
