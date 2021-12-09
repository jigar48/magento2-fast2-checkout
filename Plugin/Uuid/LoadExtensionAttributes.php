<?php

namespace Fast\Checkout\Plugin\Uuid;

use Magento\Quote\Api\Data\CartItemExtensionFactory;
use Magento\Quote\Api\Data\CartItemExtensionInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class LoadExtensionAttributes
{
    /**
     * @var CartItemExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param CartItemExtensionFactory $extensionFactory
     */
    public function __construct(
        CartItemExtensionFactory $extensionFactory
    ) {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Loads cart item entity extension attributes
     *
     * @param CartItemInterface $entity
     * @param CartItemExtensionInterface|null $extension
     * @return CartItemExtensionInterface
     */
    public function afterGetExtensionAttributes(
        CartItemInterface $entity,
        CartItemExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }
}
