<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
namespace Buhmann\StockStatus\Plugin;

use Magento\Catalog\Model\Layer\FilterList as Subject;
use Buhmann\StockStatus\Api\FilterListApplierInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class FilterList
{
    /**
     * @var FilterListApplierInterface
     */
    private FilterListApplierInterface $filterListApplier;

    /**
     * @param FilterListApplierInterface $filterListApplier
     */
    public function __construct(FilterListApplierInterface $filterListApplier)
    {
        $this->filterListApplier = $filterListApplier;
    }

    /**
     * Retrieve list of filters
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param Layer $layer
     * @return array|AbstractFilter[]
     */
    public function aroundGetFilters(
        Subject $subject,
        callable $proceed,
        Layer $layer
    ): array {
        $filters = $proceed($layer);

        return $this->filterListApplier->apply($filters, $layer);
    }
}
