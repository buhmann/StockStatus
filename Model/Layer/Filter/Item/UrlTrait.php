<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Layer\Filter\Item;

use Magento\Framework\Exception\LocalizedException;

/**
 * Trait for multi-select URL building
 *
 * Provides toggle logic for filter items in multi-select mode
 */
trait UrlTrait
{
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
