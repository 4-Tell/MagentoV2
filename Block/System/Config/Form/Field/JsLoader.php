<?php
/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */

namespace FourTell\Recommend\Block\System\Config\Form\Field;

use Magento\Backend\Block\Widget\Button;

class JsLoader extends \Magento\Config\Block\System\Config\Form\Field
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
        $html .= $this->getButtonHtml();
        $html .= '<textarea id="default_js_loader_code" style="display: none;"><!--4-Tell Recommendations Begin (www.4-tell.com)-->
<script type="text/javascript">window._4TellBoost = {};</script>
<script async type="text/javascript" id="loader4Tell" src="//4tellcdn.azureedge.net/sites/loader.js" data-sitealias="{ALIAS}" data-mode="{MODE}"></script>
<!--4-Tell Recommendations End--></textarea>';
        $html .= "<script type=\"text/javascript\">
                //<![CDATA[
                function resetLoaderCode(){
                    $('recommend_display_recommendation_js_loader').setValue($('default_js_loader_code').getValue());
                    alert('Click Save Config to save settings.');
                }
                //]]>
                </script>";
        return $html;
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'reset_js_button',
                'label' => __('Default Code'),
                'onclick' => 'resetLoaderCode();',
                'style' => 'margin-top:10px'
            ]
        );

        return $button->toHtml();
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getConflictButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id' => 'reset_js_button',
                'label' => __('jQuery No Conflict Code'),
                'onclick' => 'resetOldLoaderCode();',
                'style' => 'margin-top:10px'
            ]
        );

        return $button->toHtml();
    }
}
