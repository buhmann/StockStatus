<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Buhmann\StockStatus\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Framework\App\State;
use Magento\CatalogInventory\Api\StockRegistryInterface;

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
     * Data constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Action $productAction
     * @param State $state
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Action $productAction,
        State $state,
        StockRegistryInterface $stockRegistry
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAction = $productAction;
        $this->state = $state;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context);
    }

    /**
     * Update Stock Status Filter Attribute
     * @return $this
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
                    $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                    $isInStock = $stockItem->getIsInStock();
                    $stockStatusFilter = $product->getData(self::STOCK_STATUS_FILTER_ATTRIBUTE);

                    if ($isInStock && $stockStatusFilter != 1) {
                        $inStockProductIds[] = $product->getId();
                    } elseif (!$isInStock && $stockStatusFilter != 0) {
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

        return $this;
    }
}
