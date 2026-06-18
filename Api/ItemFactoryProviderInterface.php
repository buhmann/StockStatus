<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatus\Api;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;

/**
 * Interface for creating filter items
 *
 * This interface allows to extend or replace the item creation logic
 * without modifying the filter class itself.
 */
interface ItemFactoryProviderInterface
{
    /**
     * Create a filter item
     *
     * @param AbstractFilter $filter
     * @param string $label
     * @param mixed $value
     * @param int $count
     * @return Item
     */
    public function create(
        AbstractFilter $filter,
        string $label,
        $value,
        int $count = 0
    ): Item;
}
