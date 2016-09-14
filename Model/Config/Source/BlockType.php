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
class BlockType implements \Magento\Framework\Option\ArrayInterface
{
    const MAGENTO = 0;
    const FOURTELL = 1;
    const BOTH = 2;
    const JS = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::FOURTELL => __('4-Tell'),
            self::MAGENTO => __('Magento'),
            self::BOTH => __('Both'),
            self::JS => __('JavaScript')
        ];
    }
}