<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Config\Source;

use Buhmann\StockStatus\Api\StockStatusInterface;
use Magento\Framework\Data\OptionSourceInterface;

class PositionStrategy implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => StockStatusInterface::POSITION_FIRST, 'label' => __('First (show at the top)')],
            ['value' => StockStatusInterface::POSITION_LAST, 'label' => __('Last (show at the bottom)')],
            ['value' => StockStatusInterface::POSITION_AFTER, 'label' => __('After selected attribute')],
            ['value' => StockStatusInterface::POSITION_CUSTOM, 'label' => __('Custom position (enter number)')]
        ];
    }
}
