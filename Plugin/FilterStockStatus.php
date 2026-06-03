<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Plugin;

use Buhmann\StockStatus\Api\Data\StockStatusInterface;
use Buhmann\StockStatus\Helper\Data;
use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Buhmann\StockStatus\Model\Config\Source\Options as StockStatusOptions;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;

class FilterStockStatus
{
    /**
     * @var Data
     */
    private Data $helperData;

    /**
     * @var RequestInterface
     */
    private RequestInterface $_request;

    /**
     * @var Configurable
     */
    private Configurable $configurableType;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var AppState
     */
    private AppState $appState;

    /**
     * FilterStockStatus constructor.
     *
     * @param Data $helperData
     * @param RequestInterface $request
     * @param Configurable $configurableType
     * @param ConfigProvider $configProvider
     * @param AppState $appState
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request,
        Configurable $configurableType,
        ConfigProvider $configProvider,
        AppState $appState
    ) {
        $this->helperData = $helperData;
        $this->_request = $request;
        $this->configurableType = $configurableType;
        $this->configProvider = $configProvider;
        $this->appState = $appState;
    }

    /**
     * @param Collection $subject
     * @param \Closure $proceed
     * @param $field
     * @param null $condition
     *
     * @return Collection
     * @throws LocalizedException
     */
    public function aroundAddFieldToFilter(
        Collection $subject,
        \Closure $proceed,
                   $field,
                   $condition = null
    ) {
        if (!$this->configProvider->isStockFilterEnabled()
            || !$condition
            || $this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML
        ) {
            return $proceed($field, $condition);
        }

        if (is_object($condition)) {
            return $proceed($field, $condition);
        }

        $_field = $this->sanitizeAttrCode($field);
        $_conditions = $this->sanitizeAttrConditions($_field, $condition);

        if ($_field == StockStatusInterface::STOCK_STATUS_FILTER_ATTRIBUTE && reset($_conditions) == StockStatusOptions::IS_IN_STOCK_ATTRIBUTE_VALUE) {
            $filteredProductIds = [];
            $_subject = clone $subject;
            foreach ($_subject as $product) {
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
                $subject->addFieldToFilter('entity_id', ['nin' => $filteredProductIds]);
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
    private function getFilterData(Product $product): array
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
    private function getFinalSimpleProduct(Product $configurableProduct): ?Product
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
    private function sanitizeAttrCode(string $code = ''): string
    {
        if ($code && $this->helperData->isModuleEnabled('Smile_ElasticsuiteCore')) {
            $prefix = \Smile\ElasticsuiteCore\Helper\Mapping::OPTION_TEXT_PREFIX . '_';
            return str_replace($prefix, '', $code);
        }
        return $code;
    }

    /**
     * @param string $attrCode
     * @param mixed $conditions
     *
     * @return array
     */
    private function sanitizeAttrConditions(string $attrCode, mixed $conditions = null): array
    {
        $_conditions = [];
        if (is_string($conditions) || is_array($conditions)) {
            if (is_string($conditions)) {
                $conditions = explode(',', $conditions);
            }
            foreach ($conditions as $condition) {
                if ($condition) {
                    $_conditions[] = (int)$condition !== $condition ? $this->helperData->getAttrOptIdByLabel($attrCode, $condition) : $condition;
                }
            }
        }
        return $_conditions;
    }
}
