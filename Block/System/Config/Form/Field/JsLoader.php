<?php
/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */

namespace FourTell\Recommend\Block\System\Config\Form\Field;

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
        $html .= $this->getConflictButtonHtml();
        $html .= '<textarea id="default_old_js_loader_code" style="display: none;"><!--4-Tell Recommendations Begin (www.4-tell.com)-->
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">
    window._4TellBoost = {};
    window._4TellBoost.jq = jQuery.noConflict(true);
</script><script type="text/javascript" async src="//4tcdn.blob.core.windows.net/4tjs3/4TellLoader.js?alias=CLIENT_ALIAS&mode=MODE"></script>
</textarea>';
        $html .= '<textarea id="default_js_loader_code" style="display: none;"><!--4-Tell Recommendations Begin (www.4-tell.com)-->
<script type="text/javascript" async src="//4tcdn.blob.core.windows.net/4tjs3/4TellLoader.js?alias=CLIENT_ALIAS&mode=MODE"></script>
<script type="text/javascript">
    window._4TellBoost = {};
</script>
</textarea>';
        $html .= "<script type=\"text/javascript\">
                //<![CDATA[
                function resetLoaderCode(){
                    $('recommend_display_recommendation_js_loader').setValue($('default_js_loader_code').getValue());
                    alert('Click Save Config to save settings.');
                }
                function resetOldLoaderCode(){
                    $('recommend_display_recommendation_js_loader').setValue($('default_old_js_loader_code').getValue());
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
            'Magento\Backend\Block\Widget\Button'
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
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'reset_js_button',
                'label' => __('jQuery Conflict Code'),
                'onclick' => 'resetOldLoaderCode();',
                'style' => 'margin-top:10px'
            ]
        );

        return $button->toHtml();
    }
}