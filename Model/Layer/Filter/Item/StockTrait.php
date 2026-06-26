<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Layer\Filter\Item;

use Magento\Framework\Exception\LocalizedException;

/**
 * Trait for stock status filter item
 *
 * Provides shared logic for:
 * - Multi-select URL building with comma-separated values
 * - Selection state checking
 * - Toggle logic for adding/removing filter values
 */
trait StockTrait
{
    /**
     * Check if the current item is selected
     *
     * @return bool
     * @throws LocalizedException
     */
    public function getIsSelected(): bool
    {
        $filter = $this->getFilter();
        $selectedValues = [];

        foreach ($filter->getLayer()->getState()->getFilters() as $stateFilter) {
            if ($stateFilter->getFilter()->getRequestVar() === $filter->getRequestVar()) {
                $value = $stateFilter->getValue();
                if (is_array($value)) {
                    $selectedValues = array_merge($selectedValues, $value);
                } else {
                    $selectedValues[] = (int)$value;
                }
            }
        }

        return in_array((int)$this->getValue(), $selectedValues, true);
    }

    /**
     * Build URL for multi-select filter item
     *
     * Toggles the current value: removes if selected, adds if not selected.
     *
     * @param string $requestVar
     * @param int $currentValue
     * @param array $selectedValues
     * @return string
     */
    private function buildMultiSelectUrl(
        string $requestVar,
        int $currentValue,
        array $selectedValues
    ): string {
        $values = $selectedValues;

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
     * Build remove URL for multi-select filter item
     *
     * Removes current value from selected values.
     *
     * @param string $requestVar
     * @param int $currentValue
     * @param array $selectedValues
     * @return string
     */
    private function buildMultiSelectRemoveUrl(
        string $requestVar,
        int $currentValue,
        array $selectedValues
    ): string {
        $values = array_diff($selectedValues, [$currentValue]);

        $query = [
            $requestVar => !empty($values) ? implode(',', $values) : null,
            $this->_htmlPagerBlock->getPageVarName() => null,
        ];

        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
}
