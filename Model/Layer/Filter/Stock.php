<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

namespace Buhmann\StockStatus\Model\Layer\Filter;

use Buhmann\StockStatus\Api\ItemFactoryProviderInterface;
use Buhmann\StockStatus\ViewModel\ConfigProvider;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
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
     * @var AttributeFactory
     */
    protected AttributeFactory $eavAttributeFactory;

    /**
     * @var ItemFactoryProviderInterface
     */
    protected ItemFactoryProviderInterface $itemFactoryProvider;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Resolver $layerResolver
     * @param DataBuilder $itemDataBuilder
     * @param ConfigProvider $configProvider
     * @param AttributeFactory $eavAttributeFactory
     * @param ItemFactoryProviderInterface $itemFactoryProvider
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Resolver $layerResolver,
        DataBuilder $itemDataBuilder,
        ConfigProvider $configProvider,
        AttributeFactory $eavAttributeFactory,
        ItemFactoryProviderInterface $itemFactoryProvider,
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
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->itemFactoryProvider = $itemFactoryProvider;

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

        foreach ($this->getAvailableStatuses() as $status) {
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
    public function getAvailableStatuses(): array
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
        foreach ($this->getAvailableStatuses() as $status) {
            $labels[$status] = __($this->configProvider->getStockStatusLabel($status));
        }
        return $labels;
    }

    /**
     * Get attribute model associated with filter
     *
     * @return Attribute
     */
    public function getAttributeModel(): Attribute
    {
        $attribute = $this->_getData('attribute_model');
        if ($attribute === null) {
            $attribute = $this->eavAttributeFactory->create();
            $attribute->setId(0);
            $attribute->setAttributeCode($this->configProvider->getIndexField());
            $attribute->setFrontendLabel($this->configProvider->getFilterTitle());
            $attribute->setStoreLabel($this->configProvider->getFilterTitle());
            $attribute->setIsFilterable(1);
            $attribute->setIsVisibleOnFront(1);
            $attribute->setFacetMaxSize(count($this->getItems()));
            $attribute->setFrontendInput('select');
            $attribute->setBackendType('int');

            $this->setData('attribute_model', $attribute);
        }
        return $attribute;
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

    /**
     * Create filter item
     *
     * @param string $label
     * @param mixed $value
     * @param int $count
     * @return FilterItem
     */
    protected function _createItem($label, $value, $count = 0)
    {
        return $this->itemFactoryProvider->create($this, $label, $value, $count);
    }
}
