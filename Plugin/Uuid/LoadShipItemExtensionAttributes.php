<?php

namespace Fast\Checkout\Plugin\Uuid;

use Magento\Sales\Api\Data\ShipmentItemExtensionFactory;
use Magento\Sales\Api\Data\ShipmentItemExtensionInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;

class LoadShipItemExtensionAttributes
{
    /**
     * @var ShipmentItemExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param ShipmentItemExtensionFactory $extensionFactory
     */
    public function __construct(
        ShipmentItemExtensionFactory $extensionFactory
    ) {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Loads cart item entity extension attributes
     *
     * @param ShipmentItemInterface $entity
     * @param ShipmentItemExtensionInterface|null $extension
     * @return ShipmentItemExtensionInterface
     */
    public function afterGetExtensionAttributes(
        ShipmentItemInterface $entity,
        ShipmentItemExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }
}
