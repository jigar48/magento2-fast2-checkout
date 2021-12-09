<?php

declare(strict_types=1);

namespace Fast\Checkout\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Data patch to add show/hide fast checkout button attribute
 */
class AddShowFastAttributePatch implements DataPatchInterface
{
    const DEPENDENCIES = [];
    const ALIASES = [];
    const FAST_ATTRIBUTE = 'hide_fast_option';

    /**
     * CONST PRODUCT_GROUP
     */
    const PRODUCT_GROUP = 'Product Details';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Repository $attributeRepository
     */
    private $attributeRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Repository $attributeRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Repository $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return static::DEPENDENCIES;
    }

    /**
     * Apply patch to create hide_fast_option product attribute
     */
    public function apply()
    {
        $attributeData = [
            'type' => 'int',
            'label' => 'Show Fast Button',
            'input' => 'boolean',
            'required' => true,
            'source' => Boolean::class,
            'default' => 0,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group' => static::PRODUCT_GROUP,
            'used_in_product_listing' => true,
            'visible_on_front' => true,
            'user_defined' => false,
            'filterable' => true,
            'filterable_in_search' => true,
            'used_for_promo_rules' => true,
            'is_html_allowed_on_front' => true,
            'used_for_sort_by' => false
        ];
        try {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->addAttribute(Product::ENTITY, static::FAST_ATTRIBUTE, $attributeData);
            $attribute = $this->attributeRepository->get(static::FAST_ATTRIBUTE);
            $this->attributeRepository->save($attribute);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return static::ALIASES;
    }
}
