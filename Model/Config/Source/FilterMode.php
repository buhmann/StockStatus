<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Buhmann\StockStatus\Api\StockStatusInterface;

/**
 * Source model for filter mode configuration
 */
class FilterMode implements OptionSourceInterface
{
    /**
     * Return array of options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => StockStatusInterface::FILTER_MODE_SINGLE,
                'label' => __('Single select (radio)')
            ],
            [
                'value' => StockStatusInterface::FILTER_MODE_MULTI,
                'label' => __('Multi select (checkbox)')
            ]
        ];
    }
}
