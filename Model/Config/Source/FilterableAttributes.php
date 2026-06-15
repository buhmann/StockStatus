<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class FilterableAttributes implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $attributeCollectionFactory;

    /**
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];

        $attributes = $this->attributeCollectionFactory->create()
            ->addIsFilterableFilter()
            ->addVisibleFilter()
            ->setOrder('position', 'asc')
            ->setOrder('attribute_code', 'asc');

        foreach ($attributes as $attribute) {
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf(
                    '%s (%s)',
                    $attribute->getFrontendLabel() ?: $attribute->getAttributeCode(),
                    $attribute->getAttributeCode()
                )
            ];
        }

        return $options;
    }
}
