<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */
declare(strict_types=1);

namespace Buhmann\StockStatus\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Buhmann\StockStatus\Api\StockStatusInterface;

class StockStatusOptions implements OptionSourceInterface
{
    /**
     * Get Filtering Stock Status options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => StockStatusInterface::STOCK_IN_STOCK,
                'label' => __(StockStatusInterface::STOCK_STATUS_LABELS[StockStatusInterface::STOCK_IN_STOCK])
            ],
            [
                'value' => StockStatusInterface::STOCK_OUT_OF_STOCK,
                'label' => __(StockStatusInterface::STOCK_STATUS_LABELS[StockStatusInterface::STOCK_OUT_OF_STOCK])
            ]
        ];
    }
}
