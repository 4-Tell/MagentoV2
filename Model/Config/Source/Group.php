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
class Group implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            '0' => __('No'),
            '1' => __('Yes'),
        ];
    }
}