<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Layer\Filter\Item;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Framework\Exception\LocalizedException;

/**
 * Stock status filter item
 *
 * Extends default filter item to support multi-select URL building
 * with comma-separated values format (e.g., ?stock_status=0,1)
 */
class Stock extends Item
{
    /**
     * Get URL for filter item
     *
     * For selected items, returns URL without the filter parameter (remove filter).
     * For unselected items, returns URL with the filter parameter (apply filter).
     *
     * @return string
     * @throws LocalizedException
     */
    public function getUrl(): string
    {
        /** @var \Buhmann\StockStatus\Model\Layer\Filter\Stock $filter */
        $filter = $this->getFilter();

        // Single-select mode — use default behavior
        if (!$filter->isMultiSelectEnabled()) {
            return parent::getUrl();
        }

        // Multi-select mode — toggle logic
        $requestVar = $filter->getRequestVar();
        $currentValue = (int)$this->getValue();
        $values = $filter->getSelectedValues() ?? [];

        if (in_array($currentValue, $values)) {
            $values = array_diff($values, [$currentValue]);
        } else {
            $values[] = $currentValue;
        }

        $query = [
            $requestVar => !empty($values) ? implode(',', $values) : null,
            $this->_htmlPagerBlock->getPageVarName() => null,
        ];

        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    /**
     * Get remove URL for filter item
     *
     * Removes current value from selected values in multi-select mode.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getRemoveUrl(): string
    {
        /** @var \Buhmann\StockStatus\Model\Layer\Filter\Stock $filter */
        $filter = $this->getFilter();

        // Single-select mode — use default behavior
        if (!$filter->isMultiSelectEnabled()) {
            return parent::getRemoveUrl();
        }

        // Multi-select mode — remove current value
        $requestVar = $filter->getRequestVar();
        $currentValue = (int)$this->getValue();
        $values = $filter->getSelectedValues() ?? [];

        // Remove current value from selected values
        $values = array_diff($values, [$currentValue]);

        $query = [
            $requestVar => !empty($values) ? implode(',', $values) : null,
            $this->_htmlPagerBlock->getPageVarName() => null,
        ];

        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query, '_escape' => true]);
    }
}
