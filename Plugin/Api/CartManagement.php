<?php

namespace Fast\Checkout\Plugin\Api;

use Closure;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartSearchResultInterface;
use Magento\Quote\Model\QuoteManagement\Interceptor;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class CartManagement
 */
class CartManagement
{
    const FIELD_NAME = 'fast_order_id';

    /**
     * Cart Extension Attributes Factory
     *
     * @var CartExtensionFactory
     */
    protected $extensionFactory;

    protected $quoteRepository;

    protected $request;

    public function __construct(
        CartExtensionFactory $extensionFactory,
        QuoteRepository $quoteRepository,
        Request $request
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
    }


    public function aroundCreateEmptyCart(
        Interceptor $subject,
        Closure $proceed
    ) {
        $cartId = $proceed($subject);
        if ($this->request->getContent()) {
            $fastOrderId = json_decode($this->request->getContent())->extension_attributes->fast_order_id;
            if ($fastOrderId) {
                $cart = $this->quoteRepository->get($cartId);
                $extensionAttributes = $cart->getExtensionAttributes();
                $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
                $extensionAttributes->setFastOrderId($fastOrderId);
                $cart->setExtensionAttributes($extensionAttributes);
                $cart->setData(static::FIELD_NAME, $fastOrderId);
                $this->quoteRepository->save($cart);
            }
        }
        return $cartId;
    }
}
