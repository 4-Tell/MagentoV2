<?php
/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */

namespace FourTell\Recommend\Block\System\Config\Form\Field;

class AlternativeViews extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $html .='<link href="'.$this->getViewFileUrl("jquery/editableMultiselect/css/jquery.multiselect.css").'" type="text/css" rel="stylesheet" />';
        $html .= '<script type="text/x-magento-init">
                        {
                            "*":{
                                "mselectjs":{
                                    "data": {
                                        "myElement": "#recommend_display_recommendation_alternative_views"
                                    }
                                }
                            }
                        }
                </script>';

        return $html;
    }
}