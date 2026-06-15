<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

namespace Buhmann\StockStatus\Model\Layer\Filter;

use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;

/**
 * Stock status filter for layered navigation
 */
class Stock extends AbstractFilter
{
    /**
     * @var ConfigProvider
     */
    protected ConfigProvider $configProvider;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Resolver $layerResolver
     * @param DataBuilder $itemDataBuilder
     * @param ConfigProvider $configProvider
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Resolver $layerResolver,
        DataBuilder $itemDataBuilder,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layerResolver->get(),
            $itemDataBuilder,
            $data
        );
        $this->configProvider = $configProvider;

        $this->setRequestVar($this->configProvider->getRequestVar());
    }

    /**
     * Apply filter to collection
     *
     * @param RequestInterface $request
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function apply(RequestInterface $request)
    {
        if (!$this->configProvider->isStockFilterEnabled()) {
            return $this;
        }

        $value = $request->getParam($this->getRequestVar());
        if ($value === null) {
            return $this;
        }

        $collection = $this->getLayer()->getProductCollection();
        $indexField = $this->configProvider->getIndexField();

        $collection->addFieldToFilter($indexField, (int)$value);

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->getOptionText($value), $value)
        );

        return $this;
    }

    /**
     * Get data array for building filter items
     *
     * @return array
     * @throws LocalizedException
     */
    protected function _getItemsData(): array
    {
        if (!$this->configProvider->isStockFilterEnabled()) {
            return [];
        }

        $productCollection = $this->getLayer()->getProductCollection();
        $indexField = $this->configProvider->getIndexField();

        try {
            $facetedData = $productCollection->getFacetedData($indexField);
        } catch (\Magento\Framework\Exception\StateException) {
            return [];
        }

        if (empty($facetedData)) {
            return [];
        }

        foreach ($this->getValues() as $status) {
            $count = $facetedData[$status]['count'] ?? 0;
            if ($count > 0) {
                $this->itemDataBuilder->addItemData(
                    $this->getOptionText($status),
                    $status,
                    $count
                );
            }
        }
        return $this->itemDataBuilder->build();
    }

    /**
     * Get available stock status values from configuration
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->configProvider->getEnabledStockStatuses();
    }

    /**
     * Get labels for stock status options
     *
     * @return array
     */
    public function getLabels(): array
    {
        $labels = [];
        foreach ($this->getValues() as $status) {
            $labels[$status] = __($this->configProvider->getStockStatusLabel($status));
        }
        return $labels;
    }

    /**
     * Get attribute model associated with filter
     *
     * @return DataObject
     */
    public function getAttributeModel(): DataObject
    {
        return new DataObject([
            'store_label' => $this->configProvider->getFilterTitle(),
            'attribute_code' => $this->configProvider->getIndexField(),
            'frontend_input' => 'select',
            'is_filterable' => 1
        ]);
    }

    /**
     * Get option text from frontend model by option id
     *
     * @param int $optionId
     * @return string|bool
     */
    protected function getOptionText($optionId): bool|string
    {
        $labels = $this->getLabels();
        if (isset($labels[$optionId])) {
            return $labels[$optionId];
        }
        return '';
    }
}
