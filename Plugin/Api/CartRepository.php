<?php

namespace Fast\Checkout\Plugin\Api;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartSearchResultInterface;
use Magento\Quote\Model\CartSearchResults;


/**
 * Class CartRepository
 */
class CartRepository
{

    const FAST_ORDER_ID= 'fast_order_id';

    /**
     * Cart Extension Attributes Factory
     *
     * @var CartExtensionFactory
     */
    protected $extensionFactory;

    /**
     * CartRepositoryPlugin constructor
     *
     * @param CartExtensionFactory $extensionFactory
     */
    public function __construct(CartExtensionFactory $extensionFactory)
    {

        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Add "fast_cart_id" extension attribute to cart data object to make it accessible in API data
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $cart
     *
     * @return CartInterface
     */
    public function afterGet(CartRepositoryInterface $subject, CartInterface $quote)
    {
        $fastOrderId = $quote->getData(static::FAST_ORDER_ID);
        $extensionAttributes = $quote->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setData(static::FAST_ORDER_ID, $fastOrderId);
        $quote->setExtensionAttributes($extensionAttributes);
        return $quote;
    }

    /**
     * Add "fast_order_id" extension attribute to cart data object to make it accessible in API data
     *
     * @param CartRepositoryInterface $subject
     * @param CartSearchResultInterface $searchResult
     *
     * @return CartSearchResultInterface
     */
    public function afterGetList(CartRepositoryInterface $subject, CartSearchResults $searchResult)
    {
        $carts = $searchResult->getItems();

        foreach ($carts as &$cart) {
            $fastOrderId = $cart->getData(static::FAST_ORDER_ID);
            $extensionAttributes = $cart->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setFastOrderId($fastOrderId);
            $cart->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}
