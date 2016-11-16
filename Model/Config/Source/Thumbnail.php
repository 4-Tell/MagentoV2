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
            'thumbnail' => __('Default Thumbnail'),
            'small' => __('Default Small Image'),
            'base' => __('Default Base Image'),
            '1' => __('1'),
            '2' => __('2'),
            '3' => __('3'),
            '4' => __('4'),
            '5' => __('5'),
            '6' => __('6'),
            '7' => __('7'),
            '8' => __('8'),
            '9' => __('9'),
            '10' => __('10'),
            '11' => __('11'),
            '12' => __('12'),
            '13' => __('13'),
            '14' => __('14'),
            '15' => __('15'),
            '16' => __('16'),
            '17' => __('17'),
            '18' => __('18'),
            '19' => __('19'),
            '20' => __('20')
        ];
    }
}