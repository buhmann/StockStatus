<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Model\FilterList;

use Buhmann\StockStatus\Api\FilterListApplierInterface;
use Buhmann\StockStatus\Api\PositionProviderInterface;
use Buhmann\StockStatus\Model\Layer\Filter\StockFactory;
use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Magento\Catalog\Model\Layer;

/**
 * Default filter list applier for standard Magento
 */
class FilterListApplier implements FilterListApplierInterface
{
    /**
     * @var StockFactory
     */
    private StockFactory $stockFactory;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var PositionProviderInterface
     */
    private PositionProviderInterface $positionProvider;

    /**
     * @param StockFactory $stockFactory
     * @param ConfigProvider $configProvider
     * @param PositionProviderInterface $positionProvider
     */
    public function __construct(
        StockFactory $stockFactory,
        ConfigProvider $configProvider,
        PositionProviderInterface $positionProvider
    ) {
        $this->stockFactory = $stockFactory;
        $this->configProvider = $configProvider;
        $this->positionProvider = $positionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(array $filters, Layer $layer): array
    {
        if (!$this->configProvider->isStockFilterEnabled()) {
            return $filters;
        }

        $stockFilter = $this->stockFactory->create(['layer' => $layer]);
        $position = $this->positionProvider->getPosition($filters, $stockFilter);

        if ($position >= count($filters)) {
            $filters[] = $stockFilter;
        } elseif ($position <= 0) {
            array_unshift($filters, $stockFilter);
        } else {
            array_splice($filters, $position, 0, [$stockFilter]);
        }

        return $filters;
    }
}
