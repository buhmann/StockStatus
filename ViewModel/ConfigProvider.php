<?php
declare(strict_types=1);

namespace Buhmann\StockStatus\ViewModel;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ArgumentInterface
{
    private const CONFIG_ENABLED_XML_PATH = 'cataloginventory/filtering_stock_status/is_enabled';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if stock filter and extension are enabled
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
            self::CONFIG_ENABLED_XML_PATH,
            ScopeInterface::SCOPE_STORE
        );
        return $outOfStockEnabled && $extensionEnabled;
    }
}
