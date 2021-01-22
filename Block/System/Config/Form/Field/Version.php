<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;

class Version extends Field
{
    const MODULE_NAME = 'FourTell_Recommend';

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $version = $this->getVersion();
        //$element->setValue($version);
        return '<label class="label">'.$version.'</label>'; //$element->getElementHtml();
    }

    protected function getVersion()
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get(ModuleListInterface::class)->getOne(self::MODULE_NAME)['setup_version'];
    }
}
