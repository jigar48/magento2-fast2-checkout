<?php

namespace Fast\Checkout\Plugin;

use Closure;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Model\Order\Item;

/**
 * Class FastAttributeQuoteToOrderItem
 * @package Fast\Checkout\Plugin
 */
class FastAttributeQuoteToOrderItem
{
    /**
     * @param ToOrderItem $subject
     * @param Closure $proceed
     * @param AbstractItem $item
     * @param array $additional
     * @return Item
     */
    public function aroundConvert(
        ToOrderItem $subject,
        Closure $proceed,
        AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setFastOrderItemUuid($item->getFastOrderItemUuid());
        return $orderItem;
    }
}
