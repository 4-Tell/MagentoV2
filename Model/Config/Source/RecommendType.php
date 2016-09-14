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
class RecommendType implements \Magento\Framework\Option\ArrayInterface
{
    const CROSS = 0;
    const SIMILAR = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::CROSS => __('Cross-Sell'),
            self::SIMILAR => __('Similar')
        ];
    }
}