<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Buhmann\StockStatus\Helper;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Module\Manager as ModuleManager;

class Data extends AbstractHelper
{
    const STOCK_STATUS_FILTER_ATTRIBUTE = 'stock_status_filter';

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Action
     */
    protected $productAction;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Action $productAction
     * @param State $state
     * @param StockRegistryInterface $stockRegistry
     * @param ProductFactory $productFactory
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Action $productAction,
        State $state,
        StockRegistryInterface $stockRegistry,
        ProductFactory $productFactory,
        ModuleManager $moduleManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAction = $productAction;
        $this->state = $state;
        $this->stockRegistry = $stockRegistry;
        $this->productFactory = $productFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context);
    }

    /**
     * Update Stock Status Filter Attribute
     */
    public function updateStockStatusFilterAttribute()
    {
        try {
            if (!$this->state->getAreaCode()) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            }

            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect(self::STOCK_STATUS_FILTER_ATTRIBUTE);

            $inStockProductIds = [];
            $outOfStockProductIds = [];

            foreach ($productCollection as $product) {
                if ($product->getResource()->getAttribute(self::STOCK_STATUS_FILTER_ATTRIBUTE)) {
                    if ($this->getStockStatus($product)) {
                        $inStockProductIds[] = $product->getId();
                    } else {
                        $outOfStockProductIds[] = $product->getId();
                    }
                }
            }

            if (!empty($inStockProductIds)) {
                $this->productAction->updateAttributes($inStockProductIds, [self::STOCK_STATUS_FILTER_ATTRIBUTE => 1], 0);
            }
            if (!empty($outOfStockProductIds)) {
                $this->productAction->updateAttributes($outOfStockProductIds, [self::STOCK_STATUS_FILTER_ATTRIBUTE => ''], 0);
            }
        } catch (\Exception $e) {
            $this->_logger->error('Buhmann_StockStatus: ' . $e->getMessage());
        }
    }

    /**
     * Get stock status of product
     *
     * @param Product $product
     * @return bool
     */
    public function getStockStatus(Product $product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        return $product->isSaleable() && $stockItem->getIsInStock();
    }

    /**
     * Get attribute option
     *
     * @param $attrCode
     * @param $optLabel
     *
     * @return string
     */
    public function getAttrOptIdByLabel($attrCode, $optLabel)
    {
        $product = $this->productFactory->create();
        $isAttrExist = $product->getResource()->getAttribute($attrCode);
        $optId = '';
        if ($isAttrExist && $isAttrExist->usesSource()) {
            $optId = $isAttrExist->getSource()->getOptionId($optLabel);
        }
        return $optId;
    }

    /**
     * Check if the module is enabled
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->moduleManager->isEnabled($moduleName);
    }
}
