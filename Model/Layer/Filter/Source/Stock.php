<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Layer\Filter\Source;

use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Source model for Stock Status filter attribute
 * Provides option labels for stock status values
 */
class Stock extends AbstractSource
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Get all options for stock status filter
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->options = [];
        foreach ($this->configProvider->getEnabledStockStatuses() as $status) {
            $this->options[] = [
                'value' => $status,
                'label' => $this->configProvider->getStockStatusLabel((int)$status)
            ];
        }

        return $this->options;
    }

    /**
     * Get option text by value
     *
     * @param string|int $value
     * @return string|bool
     */
    public function getOptionText($value): bool|string
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if (isset($option['value']) && $option['value'] == $value) {
                return $option['label'] ?? $option['value'];
            }
        }
        return false;
    }
}
