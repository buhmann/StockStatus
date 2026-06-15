<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
namespace Buhmann\StockStatus\Model\Adapter\DynamicTemplates;

use Magento\OpenSearch\Model\Adapter\DynamicTemplates\MapperInterface;
use Buhmann\StockStatus\Api\StockStatusInterface;
use Buhmann\StockStatus\ViewModel\ConfigProvider;

/**
 * Dynamic template mapper for stock_status field
 * Adds stock_status field mapping to OpenSearch/Elasticsearch index
 */
class StockStatusMapper implements MapperInterface
{
    private ConfigProvider $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * Add dynamic template mapping for stock_status field.
     *
     * @param array $templates
     * @return array
     */
    public function processTemplates(array $templates): array
    {
        $indexField = $this->configProvider->getIndexField();

        $templates[] = [
            $indexField => [
                'match' => $indexField,
                'mapping' => [
                    'type' => 'integer'
                ]
            ]
        ];

        return $templates;
    }
}
