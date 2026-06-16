<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Api;

use Magento\Catalog\Model\Layer;

/**
 * Interface for applying stock status filter to filter list
 */
interface FilterListApplierInterface
{
    /**
     * Add stock status filter to filters array
     *
     * @param array $filters Existing filters
     * @param Layer $layer Current layer
     * @return array Modified filters
     */
    public function apply(array $filters, Layer $layer): array;
}
