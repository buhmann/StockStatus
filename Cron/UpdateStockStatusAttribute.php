<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Cron;

use Buhmann\StockStatus\Helper\Data as StockHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class UpdateStockStatusAttribute
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StockHelper
     */
    protected $_stockHelper;

    /**
     * UpdateStockStatusAttribute constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param StockHelper $stockHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        StockHelper $stockHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->_stockHelper = $stockHelper;
    }

    /**
     * Executes Cronjob for updating stock_status_filter attribute
     */
    public function execute()
    {
        if ($this->scopeConfig->getValue('cataloginventory/filtering_stock_status/is_enabled')) {
            $this->_stockHelper->updateStockStatusFilterAttribute();
        }
        return $this;
    }
}
