<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Model\Config\Source;


class RestrictStatusEnable extends Restrict
{
    const XML_PATH_RESTRICT = 'recommend/advanced_settings/restrict_status';
    const XML_PATH_VALUE = 'groups/advanced_settings/fields/restrict_status/value';
    const MESSAGE = 'Please specify the status.';
}