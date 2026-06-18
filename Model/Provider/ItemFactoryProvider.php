<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Provider;

use Buhmann\StockStatus\Api\ItemFactoryProviderInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;

/**
 * Default implementation of ItemFactoryProviderInterface
 *
 * Creates standard Magento filter items for layered navigation.
 */
class ItemFactoryProvider implements ItemFactoryProviderInterface
{
    /**
     * @var ItemFactory
     */
    private ItemFactory $itemFactory;

    /**
     * @param ItemFactory $itemFactory
     */
    public function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AbstractFilter $filter, string $label, $value, int $count = 0): Item
    {
        return $this->itemFactory->create()
            ->setFilter($filter)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count);
    }
}
