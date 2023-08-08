<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Options extends AbstractSource
{
    const IS_IN_STOCK_ATTRIBUTE_VALUE = 1;
    const OUT_OF_STOCK_ATTRIBUTE_VALUE = '';

    /**
     * Get Filtering Stock Status options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Out of Stock'), 'value'=> self::OUT_OF_STOCK_ATTRIBUTE_VALUE],
            ['label' => __('In Stock'), 'value'=> self::IS_IN_STOCK_ATTRIBUTE_VALUE]
        ];
        return $this->_options;
    }
}
