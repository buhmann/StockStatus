<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
namespace Buhmann\StockStatus\Plugin;

use Buhmann\StockStatus\Api\StockStatusInterface;
use Magento\Catalog\Model\Layer\FilterList as Subject;
use Buhmann\StockStatus\Model\Layer\Filter\StockFactory;
use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class FilterList
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
     * @param ConfigProvider $configProvider
     * @param StockFactory $stockFactory
     */
    public function __construct(
        ConfigProvider $configProvider,
        StockFactory $stockFactory
    ) {
        $this->configProvider = $configProvider;
        $this->stockFactory = $stockFactory;
    }

    /**
     * Retrieve list of filters
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param Layer $layer
     * @return array|AbstractFilter[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetFilters(
        Subject $subject,
        callable $proceed,
        Layer $layer
    ): array {
        $filters = $proceed($layer);

        $stockFilter = $this->stockFactory->create(['layer' => $layer]);

        if (!$this->configProvider->isStockFilterEnabled()) {
            return $filters;
        }
        $positionStrategy = $this->configProvider->getPositionStrategy();

        switch ($positionStrategy) {
            case StockStatusInterface::POSITION_FIRST:
                // Place at the beginning
                array_unshift($filters, $stockFilter);
                break;
            case StockStatusInterface::POSITION_AFTER:
                // Place after selected attribute
                $afterAttribute = $this->configProvider->getPositionAfterAttribute();
                $filters = $this->insertAfterAttribute($filters, $stockFilter, $afterAttribute);
                break;
            case StockStatusInterface::POSITION_CUSTOM:
                // Place at custom numeric position
                $position = $this->configProvider->getPositionCustom();
                $filters = $this->insertAtPosition($filters, $stockFilter, $position);
                break;
            case StockStatusInterface::POSITION_LAST:
            default:
                // Place at the end (default behavior)
                $filters[] = $stockFilter;
                break;
        }

        return $filters;
    }

    /**
     * Insert filter after specific attribute
     *
     * @param array $filters
     * @param AbstractFilter $stockFilter
     * @param string|null $attributeCode
     * @return array
     */
    private function insertAfterAttribute(array $filters, AbstractFilter $stockFilter, ?string $attributeCode): array
    {
        if ($attributeCode === null) {
            // Fallback to last position if no attribute specified
            $filters[] = $stockFilter;
            return $filters;
        }

        $insertPosition = null;

        foreach ($filters as $index => $filter) {
            $requestVar = $filter->getRequestVar();
            if ($requestVar === $attributeCode) {
                $insertPosition = $index;
                break;
            }
        }

        if ($insertPosition === null) {
            // Attribute not found, place at the end
            $filters[] = $stockFilter;
        } else {
            // Insert after the found attribute
            array_splice($filters, $insertPosition + 1, 0, [$stockFilter]);
        }

        return $filters;
    }

    /**
     * Insert filter at custom numeric position
     *
     * @param array $filters
     * @param AbstractFilter $stockFilter
     * @param int|null $position
     * @return array
     */
    private function insertAtPosition(array $filters, AbstractFilter $stockFilter, ?int $position): array
    {
        if ($position === null || $position < 0) {
            // Invalid position, place at the end
            $filters[] = $stockFilter;
            return $filters;
        }

        if ($position >= count($filters)) {
            // Position is beyond array length, place at the end
            $filters[] = $stockFilter;
        } elseif ($position === 0) {
            // Place at the beginning
            array_unshift($filters, $stockFilter);
        } else {
            // Insert at specified position
            array_splice($filters, $position, 0, [$stockFilter]);
        }

        return $filters;
    }
}
