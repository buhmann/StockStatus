<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Api;

use Magento\CatalogInventory\Model\Stock as InventoryStockModel;

/**
 * Interface StockStatusInterface
 * @package Buhmann\StockStatus\Api
 */
interface StockStatusInterface
{
    /**
     * Index field name in Elasticsearch/OpenSearch
     */
    public const string INDEX_FIELD = 'stock_status';

    /**
     * Configuration paths
     */
    public const string CONFIG_ENABLED_PATH = 'catalog/layered_navigation/stock_status_enabled';
    public const string CONFIG_TITLE_PATH = 'catalog/layered_navigation/stock_status_title';
    public const string CONFIG_AVAILABLE_STATES_PATH = 'catalog/layered_navigation/stock_status_available';
    public const string CONFIG_REQUEST_VAR_PATH = 'catalog/layered_navigation/stock_status_request_var';
    public const string CONFIG_POSITION_STRATEGY_PATH = 'catalog/layered_navigation/stock_status_position';
    public const string CONFIG_POSITION_ATTRIBUTE_PATH = 'catalog/layered_navigation/stock_status_position_attribute';
    public const string CONFIG_POSITION_CUSTOM_PATH = 'catalog/layered_navigation/stock_status_position_custom';

    /**
     * Position strategy values
     */
    public const string POSITION_FIRST = 'first';
    public const string POSITION_LAST = 'last';
    public const string POSITION_AFTER = 'after';
    public const string POSITION_CUSTOM = 'custom';

    /**
     * Stock status values
     */
    public const int STOCK_IN_STOCK = InventoryStockModel::STOCK_IN_STOCK;
    public const int STOCK_OUT_OF_STOCK = InventoryStockModel::STOCK_OUT_OF_STOCK;

    /**
     * Stock status option labels
     */
    public const array STOCK_STATUS_LABELS = [
        self::STOCK_IN_STOCK => 'In Stock',
        self::STOCK_OUT_OF_STOCK => 'Out of Stock'
    ];
}
