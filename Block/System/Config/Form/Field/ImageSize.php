<?php

namespace FourTell\Recommend\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ImageSize extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setStyle('width:70px;')->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = [];
        }

        $horizontal = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $vertical = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        return __(
            '<label class="label"><span>Horizontal</span></label>'
        ) . $horizontal . __(
            '<label class="label"><span>Vertical</span></label>'
        ) . $vertical;
    }
}