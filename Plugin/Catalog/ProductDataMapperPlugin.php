<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
namespace Buhmann\StockStatus\Plugin\Catalog;

use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper as Subject;

/**
 * Plugin to add stock_status field to Elasticsearch/OpenSearch index data
 */
class ProductDataMapperPlugin
{
    private ResourceConnection $resourceConnection;
    private ConfigProvider $configProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ConfigProvider $configProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configProvider = $configProvider;
    }

    /**
     * Add stock_status field to index data for search engine metadata
     *
     * @param Subject $subject
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMap(
        Subject $subject,
        array $documentData,
        $storeId = 0,
        $context = []
    ): array {
        if (!$this->configProvider->isStockFilterEnabled()) {
            return $documentData;
        }

        $productIds = array_keys($documentData);

        if (empty($productIds)) {
            return $documentData;
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('cataloginventory_stock_status'),
                ['product_id', 'stock_status']
            )
            ->where('product_id IN (?)', $productIds);

        $stockStatuses = $connection->fetchPairs($select);
        $indexField = $this->configProvider->getIndexField();

        foreach ($productIds as $productId) {
            $documentData[$productId][$indexField] = (int)($stockStatuses[$productId] ?? 0);
        }

        return $documentData;
    }
}
