<?php

/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Model\Config\Source;

class Thumbnail implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            '0' => __('Default'),
            '1' => __('1'),
            '2' => __('2'),
            '3' => __('3'),
            '4' => __('4'),
            '5' => __('5'),
            '6' => __('6'),
            '7' => __('7'),
            '8' => __('8'),
            '9' => __('9'),
            '10' => __('10')
        ];
    }
}