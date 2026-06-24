<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
namespace Buhmann\StockStatus\Model\Layer\Filter\DataProvider;

use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Exception;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;

class Stock
{
    /**
     * @var array|null
     */
    protected ?array $totalCounts = null;

    /**
     * @var Layer
     */
    protected Layer $layer;

    /**
     * @var ConfigProvider
     */
    protected ConfigProvider $configProvider;

    /**
     * @param Resolver $layerResolver
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        Resolver $layerResolver,
        ConfigProvider $configProvider
    ) {
        $this->layer = $layerResolver->get();
        $this->configProvider = $configProvider;
    }

    /**
     * Get total counts for all stock statuses without stock_status filter
     *
     * @return array
     */
    public function getTotalCounts(): array
    {
        if ($this->totalCounts === null) {
            return $this->collectTotalCounts();
        }
        return $this->totalCounts;
    }

    /**
     * Compute calculation of product counts against the engine.
     *
     * @return array
     */
    public function collectTotalCounts(): array
    {
        if ($this->totalCounts !== null) {
            return $this->totalCounts;
        }

        $collection = $this->layer->getProductCollection();
        $indexField = $this->configProvider->getIndexField();

        try {
            $cloneCollection = clone $collection;
            $facetedData = $cloneCollection->getFacetedData($indexField);
            $this->totalCounts = [];
            foreach ($facetedData as $status => $data) {
                $this->totalCounts[(int)$status] = (int)($data['count'] ?? 0);
            }
        } catch (Exception) {
            $this->totalCounts = [];
        }

        return $this->totalCounts;
    }
}
