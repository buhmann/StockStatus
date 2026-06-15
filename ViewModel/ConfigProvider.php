<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\ViewModel;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Buhmann\StockStatus\Api\StockStatusInterface;

/**
 * ConfigProvider for Stock Status Filter
 * Provides configuration values for the stock status filter functionality
 */
class ConfigProvider implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if stock filter extension is enabled
     *
     * @return bool
     */
    public function isStockFilterEnabled(): bool
    {
        $outOfStockEnabled = $this->scopeConfig->isSetFlag(
            Configuration::XML_PATH_DISPLAY_PRODUCT_STOCK_STATUS,
            ScopeInterface::SCOPE_STORE
        );
        $extensionEnabled = $this->scopeConfig->isSetFlag(
            StockStatusInterface::CONFIG_ENABLED_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return $outOfStockEnabled && $extensionEnabled;
    }

    /**
     * Get filter title from config or fallback to default
     *
     * @return string
     */
    public function getFilterTitle(): string
    {
        $title = $this->scopeConfig->getValue(
            StockStatusInterface::CONFIG_TITLE_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return !empty($title) ? $title : __('Stock Status')->render();
    }

    /**
     * Get request variable name from config
     * Falls back to INDEX_FIELD if config value is empty
     *
     * @return string
     */
    public function getRequestVar(): string
    {
        $requestVar = $this->scopeConfig->getValue(
            StockStatusInterface::CONFIG_REQUEST_VAR_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return !empty($requestVar)
            ? $requestVar
            : $this->getIndexField();
    }

    /**
     * Get index field name
     *
     * @return string
     */
    public function getIndexField(): string
    {
        return StockStatusInterface::INDEX_FIELD;
    }

    /**
     * Get enabled stock status values for filter
     * Returns array of status values configured in system config
     *
     * @return array
     */
    public function getEnabledStockStatuses(): array
    {
        $available = $this->scopeConfig->getValue(
            StockStatusInterface::CONFIG_AVAILABLE_STATES_PATH,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($available)) {
            return [
                StockStatusInterface::STOCK_IN_STOCK,
                StockStatusInterface::STOCK_OUT_OF_STOCK
            ];
        }

        return explode(',', $available);
    }

    /**
     * Get stock status label by status value
     *
     * @param int $status
     * @return string
     */
    public function getStockStatusLabel(int $status): string
    {
        return __(StockStatusInterface::STOCK_STATUS_LABELS[$status] ?? '')->render();
    }

    /**
     * Get filter position strategy
     * Returns one of: first, last, after, custom
     *
     * @return string
     */
    public function getPositionStrategy(): string
    {
        $strategy = $this->scopeConfig->getValue(
            StockStatusInterface::CONFIG_POSITION_STRATEGY_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return $strategy ?: StockStatusInterface::POSITION_LAST;
    }

    /**
     * Get attribute code to place after
     * Only applicable when strategy is 'after'
     *
     * @return string|null
     */
    public function getPositionAfterAttribute(): ?string
    {
        if ($this->getPositionStrategy() !== StockStatusInterface::POSITION_AFTER) {
            return null;
        }

        $attribute = $this->scopeConfig->getValue(
            StockStatusInterface::CONFIG_POSITION_ATTRIBUTE_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return !empty($attribute) ? $attribute : null;
    }

    /**
     * Get custom sort order value
     * Only applicable when strategy is 'custom'
     *
     * @return int|null
     */
    public function getPositionCustom(): ?int
    {
        if ($this->getPositionStrategy() !== StockStatusInterface::POSITION_CUSTOM) {
            return null;
        }

        $position = $this->scopeConfig->getValue(
            StockStatusInterface::CONFIG_POSITION_CUSTOM_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return $position !== null ? (int)$position : null;
    }

    /**
     * Get full position configuration as array
     * Returns complete position config with all relevant parameters
     *
     * @return array
     */
    public function getPositionConfig(): array
    {
        $config = [
            'strategy' => $this->getPositionStrategy()
        ];

        switch ($config['strategy']) {
            case StockStatusInterface::POSITION_AFTER:
                $config['attribute'] = $this->getPositionAfterAttribute();
                break;
            case StockStatusInterface::POSITION_CUSTOM:
                $config['position'] = $this->getPositionCustom();
                break;
        }

        return $config;
    }
}
