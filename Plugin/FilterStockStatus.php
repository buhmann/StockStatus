<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Plugin;

use Buhmann\StockStatus\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class FilterStockStatus
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Configurable
     */
    protected $configurableType;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;


    public function __construct(
        RequestInterface $request,
        Configurable $configurableType,
        StockRegistryInterface $stockRegistry
    ) {
        $this->_request = $request;
        $this->configurableType = $configurableType;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param Collection $subject
     * @param \Closure $proceed
     * @param $field
     * @param null $condition
     *
     * @return Collection
     */
    public function aroundAddFieldToFilter(
        Collection $subject,
        \Closure $proceed,
        $field,
        $condition = null
    ) {
        if ($field == Data::STOCK_STATUS_FILTER_ATTRIBUTE && $condition == '1') {
            $filteredProductIds = [];
            foreach ($subject as $product) {
                if (!$this->getStockStatus($product)) {
                    $filteredProductIds[] = $product->getId();
                    $subject->removeItemByKey($product->getId());
                } else if($product->getTypeId() == Configurable::TYPE_CODE) {
                    $childProduct = $this->getFinalSimpleProduct($product);
                    if ($childProduct && !$childProduct->isSaleable()) {
                        $filteredProductIds[] = $product->getId();
                        $subject->removeItemByKey($product->getId());
                    }
                }
            }

            if (!empty($filteredProductIds)) {
                $subject->addAttributeToFilter('entity_id', ['nin' => $filteredProductIds]);
            }

            return $subject;
        } else {
            return $proceed($field, $condition);
        }
    }

    /**
     * Get used product attributes
     *
     * @param Product $product
     *
     * @return array
     */
    protected function getFilterData(Product $product)
    {
        $filterValues = [];
        $filterData = $this->_request->getParams();
        foreach ($this->configurableType->getUsedProductAttributes($product) as $attribute) {
            $code = $attribute->getData('attribute_code');
            if (isset($filterData[$code])) {
                $filterValues[$code] = $filterData[$code];
            }
        }
        return $filterValues;
    }

    /**
     * Get stock status of product
     *
     * @param Product $product
     * @return bool
     */
    protected function getStockStatus(Product $product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        return $product->isSaleable() && $stockItem->getIsInStock();
    }

    /**
     * Get final simple product by filter data
     *
     * @param Product $configurableProduct
     *
     * @return Product|null
     */
    protected function getFinalSimpleProduct(Product $configurableProduct)
    {
        $filterData = $this->getFilterData($configurableProduct);
        $childProducts = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
        /** @var Product $childProduct */
        foreach ($childProducts as $childProduct) {
            $match = true;
            foreach ($filterData as $attributeCode => $value) {
                if ($childProduct->getData($attributeCode) != $value) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                return $childProduct;
            }
        }
        return null;
    }
}
