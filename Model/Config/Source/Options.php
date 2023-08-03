<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Buhmann\StockStatus\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Options extends AbstractSource
{
    /**
     * Get Filtering Stock Status options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Out of Stock'), 'value'=> ''],
            ['label' => __('In Stock'), 'value'=> 1]
        ];
        return $this->_options;
    }
}
