<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Api;

/**
 * Interface for mapping stock_status field to index
 */
interface IndexFieldMapperInterface
{
    /**
     * Get field name for indexing
     *
     * @return string
     */
    public function getIndexField(): string;
}
