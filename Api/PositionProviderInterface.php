<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Api;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Interface for determining position of stock status filter in filter list
 */
interface PositionProviderInterface
{
    /**
     * Get position index for stock status filter
     *
     * @param array $filters Existing filters list
     * @param AbstractFilter $stockFilter Stock status filter instance
     * @return int Position index (0 = first, count = last)
     */
    public function getPosition(array $filters, AbstractFilter $stockFilter): int;
}
