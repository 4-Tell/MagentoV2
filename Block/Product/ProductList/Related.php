<?php
/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */

namespace FourTell\Recommend\Block\Product\ProductList;

/**
 * Catalog product related items block
 *
 */
class Related extends \Magento\Catalog\Block\Product\ProductList\Related
{
    protected function isRelatedHide()
    {
        $configEnabled = $this->_scopeConfig->getValue(
            \FourTell\Recommend\Helper\Data::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $hideMageUpsell = $this->_scopeConfig->getValue(
            \FourTell\Recommend\Helper\Data::XML_PATH_HIDE_RELATED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($configEnabled && $hideMageUpsell) {
            return true;
        }

        return false;
    }

    /**
     * Produce and return block's html output
     *
     * This method should not be overridden. You can override _toHtml() method in descendants if needed.
     *
     * @return string
     */
    public function _toHtml()
    {
        if ($this->isRelatedHide())
            return '';
        return parent::_toHtml();
    }
}