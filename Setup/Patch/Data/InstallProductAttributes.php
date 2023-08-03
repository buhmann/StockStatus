<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Setup\Patch\Data;

use Buhmann\StockStatus\Helper\Data as HelperData;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class InstallProductAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallInitialConfigurableAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $productAttributesData = [
            HelperData::STOCK_STATUS_FILTER_ATTRIBUTE => [
                'type' => 'int',
                'label' => 'Filtering Stock Status',
                'input' => 'select',
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => true,
                'comparable' => false,
                'visible' => true,
                'visible_in_advanced_search' => true,
                'visible_on_front' => false,
                'apply_to' => implode(',', [Type::TYPE_SIMPLE, Configurable::TYPE_CODE]),
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
                'used_in_product_listing' => false,
                'class' => '',
                'backend' => '',
                'frontend' => '',
                'group' => 'General',
                'source' => \Buhmann\StockStatus\Model\Config\Source\Options::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'default' => 1,
                'unique' => false,
                'is_user_defined' => true,
            ],
        ];

        foreach($productAttributesData as $attributeCode => $attrInfo){
            $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
            $eavSetup->addAttribute(
                Product::ENTITY,
                $attributeCode,
                $attrInfo
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '0.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
