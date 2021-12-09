<?php

namespace Fast\Checkout\Plugin\Uuid;

use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;

class LoadQuoteExtensionAttributes
{
    /**
     * @var CartExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param CartExtensionFactory $extensionFactory
     */
    public function __construct(
        CartExtensionFactory $extensionFactory
    ) {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Loads cart item entity extension attributes
     *
     * @param CartInterface $entity
     * @param CartExtensionInterface|null $extension
     * @return CartExtensionInterface
     */
    public function afterGetExtensionAttributes(
        CartInterface $entity,
        CartExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }
}
