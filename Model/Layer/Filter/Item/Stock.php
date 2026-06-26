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
    use StockTrait;

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

        if (!$filter->isMultiSelectEnabled()) {
            return parent::getUrl();
        }

        return $this->buildMultiSelectUrl(
            $filter->getRequestVar(),
            (int)$this->getValue(),
            $filter->getSelectedValues() ?? []
        );
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

        if (!$filter->isMultiSelectEnabled()) {
            return parent::getRemoveUrl();
        }

        return $this->buildMultiSelectRemoveUrl(
            $filter->getRequestVar(),
            (int)$this->getValue(),
            $filter->getSelectedValues() ?? []
        );
    }
}
