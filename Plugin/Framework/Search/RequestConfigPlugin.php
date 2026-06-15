<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
namespace Buhmann\StockStatus\Plugin\Framework\Search;

use Magento\Framework\Search\Request\Config;
use Magento\Framework\App\RequestInterface;
use Buhmann\StockStatus\Api\StockStatusInterface;
use Buhmann\StockStatus\ViewModel\ConfigProvider;

/**
 * Plugin to dynamically add stock_status filter and aggregation to search request configuration
 */
class RequestConfigPlugin
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * List of request names that should be modified
     */
    private const array SUPPORTED_REQUESTS = [
        'catalog_view_container',  // Category page layered navigation
        'quick_search_container'    // Search results page
    ];

    /**
     * @param RequestInterface $request
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        RequestInterface $request,
        ConfigProvider $configProvider
    ) {
        $this->request = $request;
        $this->configProvider = $configProvider;
    }

    /**
     * Add stock_status filter definition, aggregation and query reference to search request config
     *
     * @param Config $subject
     * @param array $result
     * @param string|null $requestName
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(Config $subject, array $result, ?string $requestName = null): array
    {
        // Only modify supported requests (category and search pages)
        if (!in_array($requestName, self::SUPPORTED_REQUESTS)) {
            return $result;
        }

        // Check if stock status filter is enabled in configuration
        if (!$this->configProvider->isStockFilterEnabled()) {
            return $result;
        }

        // Get configured values
        $stockStatus = $this->request->getParam($this->configProvider->getRequestVar());
        $indexField = $this->configProvider->getIndexField();

        // Add query definition for stock_status filter
        if (!isset($result['queries'][$indexField])) {
            $result['queries'][$indexField] = [
                'name' => $indexField,
                'type' => 'filteredQuery',
                'filterReference' => [
                    [
                        'clause' => 'must',
                        'ref' => $indexField . '_filter'
                    ]
                ]
            ];
        }

        // Add term filter definition for stock_status field
        if (!isset($result['filters'][$indexField . '_filter'])) {
            $result['filters'][$indexField . '_filter'] = [
                'name' => $indexField . '_filter',
                'type' => 'termFilter',
                'field' => $indexField,
                'value' => '$' . $indexField . '$'
            ];
        }

        // Add aggregation bucket for stock_status to enable filter counts in layered navigation
        if (!isset($result['aggregations'][$indexField . '_bucket'])) {
            $result['aggregations'][$indexField . '_bucket'] = [
                'name' => $indexField . '_bucket',
                'type' => 'termBucket',
                'field' => $indexField,
                'metrics' => [
                    ['type' => 'count']
                ]
            ];
        }

        // Add stock_status query reference to the main boolQuery
        if (isset($result['queries'][$requestName]['queryReference'])) {
            $queryReferences = $result['queries'][$requestName]['queryReference'];
            $hasStockStatus = false;

            foreach ($queryReferences as $ref) {
                if (isset($ref['ref']) && $ref['ref'] === $indexField) {
                    $hasStockStatus = true;
                    break;
                }
            }

            if (!$hasStockStatus) {
                $result['queries'][$requestName]['queryReference'][] = [
                    'clause' => 'must',
                    'ref' => $indexField
                ];
            }
        }

        // If stock_status parameter is present, add it to binds
        if ($stockStatus !== null) {
            if (!isset($result['binds'])) {
                $result['binds'] = [];
            }
            $result['binds'][$indexField] = [
                'value' => (int)$stockStatus
            ];
        }

        return $result;
    }
}
