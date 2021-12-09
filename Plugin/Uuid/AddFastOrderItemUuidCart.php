<?php

namespace Fast\Checkout\Plugin\Uuid;

use Closure;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

/**
 * Class AddFastOrderItemUuidCart
 */
class AddFastOrderItemUuidCart
{
    const FIELD_NAME = 'fast_order_item_uuid';

    /**
     * @param CartItemRepositoryInterface $subject
     * @param Closure $proceed
     * @param $cartId
     * @return array
     */
    public function aroundGetList(CartItemRepositoryInterface $subject, Closure $proceed, $cartId)
    {
        $cartItems = $proceed($cartId);
        $items = [];
        foreach ($cartItems as $orderItem) {
            $fastOrderItemUuid = $orderItem->getData(static::FIELD_NAME);
            $extensionAttributes = $orderItem->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setFastOrderItemUuid($fastOrderItemUuid);
            $orderItem->setExtensionAttributes($extensionAttributes);
            $items[] = $orderItem;
        }

        return $items;
    }
}
