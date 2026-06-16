<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Provider;

use Buhmann\StockStatus\Api\PositionProviderInterface;
use Buhmann\StockStatus\Api\StockStatusInterface;
use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Default position provider based on admin configuration
 *
 * Determines the position of stock status filter in layered navigation
 * based on configuration settings: first, last, after attribute, or custom position.
 */
class PositionProvider implements PositionProviderInterface
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition(array $filters, AbstractFilter $stockFilter): int
    {
        $strategy = $this->configProvider->getPositionStrategy();

        switch ($strategy) {
            case StockStatusInterface::POSITION_FIRST:
                return 0;

            case StockStatusInterface::POSITION_AFTER:
                $position = $this->findPositionAfterAttribute($filters);
                return $position !== null ? $position + 1 : count($filters);

            case StockStatusInterface::POSITION_CUSTOM:
                $position = $this->configProvider->getPositionCustom();
                return $this->normalizePosition($position, count($filters));

            case StockStatusInterface::POSITION_LAST:
            default:
                return count($filters);
        }
    }

    /**
     * Find the index of the attribute to place the filter after
     *
     * @param array $filters List of existing filters
     * @return int|null Index of the target attribute, or null if not found
     */
    protected function findPositionAfterAttribute(array $filters): ?int
    {
        $afterAttribute = $this->configProvider->getPositionAfterAttribute();
        if ($afterAttribute === null) {
            return null;
        }

        foreach ($filters as $index => $filter) {
            if ($filter->getRequestVar() === $afterAttribute) {
                return $index;
            }
        }
        return null;
    }

    /**
     * Normalize position value to valid range
     *
     * @param int|null $position Raw position value from config
     * @param int $total Total number of filters
     * @return int Normalized position within [0, total] range
     */
    protected function normalizePosition(?int $position, int $total): int
    {
        if ($position === null || $position < 0) {
            return $total;
        }
        if ($position > $total) {
            return $total;
        }
        return $position;
    }
}
