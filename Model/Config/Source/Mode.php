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
class Mode implements \Magento\Framework\Option\ArrayInterface
{

    const LIVE = 'live';
    const STAGE = 'stage';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::LIVE => __('Live'),
            self::STAGE => __('Stage')
        ];
    }
}