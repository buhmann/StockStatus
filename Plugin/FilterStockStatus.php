<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Plugin;

use Buhmann\StockStatus\Helper\Data;
use Buhmann\StockStatus\Model\Config\Source\Options as StockStatusOptions;
use Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Smile\ElasticsuiteCore\Helper\Mapping;

class FilterStockStatus
{
    private $helperData;
    /**
     * @var RequestInterface
     */
    private $_request;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * FilterStockStatus constructor.
     *
     * @param Data $helperData
     * @param RequestInterface $request
     * @param Configurable $configurableType
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request,
        Configurable $configurableType,
        StockRegistryInterface $stockRegistry
    ) {
        $this->helperData = $helperData;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddFieldToFilter(
        Collection $subject,
        \Closure $proceed,
        $field,
        $condition = null
    ) {
        $_field = $this->sanitizeAttrCode($field);
        $_conditions = $this->sanitizeAttrConditions($_field, $condition);
        if ($_field == Data::STOCK_STATUS_FILTER_ATTRIBUTE && reset($_conditions) == StockStatusOptions::IS_IN_STOCK_ATTRIBUTE_VALUE) {
            $filteredProductIds = [];
            foreach ($subject as $product) {
                if (!$this->helperData->getStockStatus($product)) {
                    $filteredProductIds[] = $product->getId();
                    $subject->removeItemByKey($product->getId());
                } else if($product->getTypeId() == Configurable::TYPE_CODE) {
                    $childProduct = $this->getFinalSimpleProduct($product);
                    if ($childProduct && !$this->helperData->getStockStatus($childProduct)) {
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
    private function getFilterData(Product $product)
    {
        $filterValues = [];
        $filterData = $this->_request->getParams();
        foreach ($this->configurableType->getUsedProductAttributes($product) as $attribute) {
            $code = $attribute->getData('attribute_code');
            if (isset($filterData[$code])) {
                $filterValues[$code] = $this->sanitizeAttrConditions($code, $filterData[$code]);
            }
        }
        return $filterValues;
    }

    /**
     * Get final simple product by filter data
     *
     * @param Product $configurableProduct
     *
     * @return Product|null
     */
    private function getFinalSimpleProduct(Product $configurableProduct)
    {
        $filterData = $this->getFilterData($configurableProduct);
        $childProducts = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);

        /** @var Product $childProduct */
        foreach ($childProducts as $childProduct) {
            $match = true;
            foreach ($filterData as $attributeCode => $values) {
                foreach ($values as $value) {
                    if ($childProduct->getData($attributeCode) != $value) {
                        $match = false;
                        break;
                    }
                }
            }

            if ($match) {
                return $childProduct;
            }
        }
        return null;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function sanitizeAttrCode($code = '')
    {
        if ($code && $this->helperData->isModuleEnabled('Smile_ElasticsuiteCore')) {
            $prefix = Mapping::OPTION_TEXT_PREFIX . '_';
            return str_replace($prefix, '', $code);
        }
        return $code;
    }

    /**
     * @param string $attrCode
     * @param string|array $conditions
     *
     * @return array|string
     */
    private function sanitizeAttrConditions($attrCode, $conditions)
    {
        if ($conditions && $this->helperData->isModuleEnabled('Smile_ElasticsuiteCore')) {
            if (!is_array($conditions)) {
                $conditions = explode(',', $conditions);
            }
            $_conditions = [];
            foreach ($conditions as $condition) {
                $_conditions[] = $this->helperData->getAttrOptIdByLabel($attrCode, $condition);
            }
            return $_conditions;
        }
        return $conditions = is_array($conditions) ? $conditions : explode(',', $conditions);
    }
}
