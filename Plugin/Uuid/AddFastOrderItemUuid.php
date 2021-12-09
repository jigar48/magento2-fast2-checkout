<?php

namespace Fast\Checkout\Plugin\Uuid;

use Closure;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\GuestCart\GuestCartItemRepository as ItemRepository;
use Magento\Quote\Model\Quote\Item\Repository\Interceptor;

/**
 * Class AddFastOrderItemUuid
 */
class AddFastOrderItemUuid
{
    const FIELD_NAME = 'fast_order_item_uuid';
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * AddFastOrderItemUuid constructor.
     * @param ItemRepository $cartItemRepository
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ItemRepository $cartItemRepository,
        ResourceConnection $resourceConnection
    ) {
        $this->cartItemRepository = $cartItemRepository;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Interceptor $subject
     * @param Closure $proceed
     * @param CartItemInterface $entity
     * @return array|AbstractModel|CartItemInterface
     * @throws Exception
     */
    public function aroundSave(
        Interceptor $subject,
        Closure $proceed,
        CartItemInterface $entity
    ) {
        $extensionAttributes = $entity->getExtensionAttributes();
        $fastOrderItemUuid = $extensionAttributes->getFastOrderItemUuid();
        $quoteId = $entity->getQuoteId();

        if ($extensionAttributes == null || $extensionAttributes->getFastOrderItemUuid() == null || !$quoteId) {
            return [$entity];
        }

        /** @var CartItemInterface|AbstractModel $cartItem */
        $cartItem = $proceed($entity);
        if (!$cartItem) {
            return $cartItem;
        }
        $cartItem->setData('fast_order_item_uuid', $fastOrderItemUuid);
        $cartItem->setExtensionAttributes($extensionAttributes);
        $cartItem->setQuoteId($quoteId);
        try {
            $connection = $this->resourceConnection->getConnection();
            $query = "UPDATE `quote_item` SET `fast_order_item_uuid`= '" . $fastOrderItemUuid . "' WHERE item_id = " . $cartItem->getItemId();
            $connection->query($query);
        } catch (Exception $e) {
            throw $e;
        }
        return $cartItem;
    }

    /**
     * @param Interceptor $subject
     * @param $orderItems
     * @return array
     */
    public function afterGetList(Interceptor $subject, $orderItems)
    {
        $items = [];
        foreach ($orderItems as $orderItem) {
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
