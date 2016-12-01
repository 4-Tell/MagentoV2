<?php

/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Model\Config\Source;

class Thumbnail implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Attribute
     */
    private $attributeHelper;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $positions = [
            array('value' => '1', 'label' => __('1')),
            array('value' => '2', 'label' => __('2')),
            array('value' => '3', 'label' => __('3')),
            array('value' => '4', 'label' => __('4')),
            array('value' => '5', 'label' => __('5')),
            array('value' => '6', 'label' => __('6')),
            array('value' => '7', 'label' => __('7')),
            array('value' => '8', 'label' => __('8')),
            array('value' => '9', 'label' => __('9')),
            array('value' => '10', 'label' => __('10')),
            array('value' => '11', 'label' => __('11')),
            array('value' => '12', 'label' => __('12')),
            array('value' => '13', 'label' => __('13')),
            array('value' => '14', 'label' => __('14')),
            array('value' => '15', 'label' => __('15')),
            array('value' => '16', 'label' => __('16')),
            array('value' => '17', 'label' => __('17')),
            array('value' => '18', 'label' => __('18')),
            array('value' => '19', 'label' => __('19')),
            array('value' => '20', 'label' => __('20'))
        ];
        $sort = ['thumbnail', 'small_image', 'image'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributes = $objectManager->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection');
        $attributes->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4);
        $attributes->addFieldToFilter('attribute_code', array('in' => $this->getMediaAttributeCodes()))->load();
        $mediaAttributes = array();
        foreach($attributes->getItems() as $attribute){
            $mediaAttributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        foreach ($sort as $key){
            if (isset($mediaAttributes[$key]))
                $result[$key] = $mediaAttributes[$key];
        }
        $result = $result + $positions;
        $result = array_merge($result,$mediaAttributes);
        return $result;
    }

    /**
     * @return array
     */
    public function getMediaAttributeCodes()
    {
        return $this->getAttributeHelper()->getAttributeCodesByFrontendType('media_image');
    }

    /**
     * @return Attribute
     */
    private function getAttributeHelper()
    {
        if (null === $this->attributeHelper) {
            $this->attributeHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Eav\Model\Entity\Attribute');
        }
        return $this->attributeHelper;
    }
}