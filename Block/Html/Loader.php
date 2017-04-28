<?php
/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */
namespace FourTell\Recommend\Block\Html;

/**
 * Class Loader
 * @package FourTell\Recommend\Block\Html
 */
class Loader extends \Magento\Framework\View\Element\Template
{
    /**
     * Recommend helper
     *
     * @var \FourTell\Recommend\Helper\Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FourTell\Recommend\Helper\Data $helper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    /**
     * Checks whether module Enabled (4-Tell Boost Service, General Settings)
     *
     * @return bool
     */
    public function isConfigEnable()
    {
        $configEnabled = $this->_scopeConfig->getValue(
            \FourTell\Recommend\Helper\Data::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($configEnabled) {
            return true;
        }

        return false;
    }

    /**
     * Returns the code to be used to load external JS
     *
     * @return string
     */
    public function getJsLoader()
    {
        $jsCode = $this->_scopeConfig->getValue(
            \FourTell\Recommend\Helper\Data::XML_PATH_JS_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $jsCode;
    }

    /**
     * Returns the Mode
     *
     * @return string
     */
    public function getConfigMode()
    {
        $jsCode = $this->_scopeConfig->getValue(
            \FourTell\Recommend\Helper\Data::XML_PATH_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $jsCode;
    }

    /**
     * Returns the Alias
     *
     * @return string
     */
    public function getConfigAlias()
    {
        $jsCode = $this->_scopeConfig->getValue(
            \FourTell\Recommend\Helper\Data::XML_PATH_CLIENT_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $jsCode;
    }

    /**
     * Returns parameters that should appear in the loader code
     *
     * @return string
     */
    public function getVariables()
    {
        $res = $this->_helper->getVariables();
        return $res;
    }

    /**
     * Returns parameters that should appear in the loader code
     *
     * @return string
     */
    public function getExtraData()
    {
        $res = $this->_helper->getExtraData();
        return $res;
    }
}