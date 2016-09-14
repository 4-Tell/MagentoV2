<?php

/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Model\Config\Source;

/**
 * AdminNotification mode source
 *
 * @codeCoverageIgnore
 */
class FillMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'All' => __('All'),
            'Genomic' => __('Genomic'),
            'Crowd' => __('Crowd'),
            'Strict' => __('Strict'),
            'None' => __('None')
        ];
    }
}