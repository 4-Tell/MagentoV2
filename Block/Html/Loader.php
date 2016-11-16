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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
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
     * Returns ProductIds, according to product type of the product being viewed and config rules
     *
     * @param $product
     * @return string
     */
    public function getProductIds($product)
    {
        $productIds = [];
        $productType = $product->getTypeID();

        switch ($productType) {
            case 'configurable':
                $productIds[] = $product->getId();
                break;
            case 'grouped':
                if ($this->_scopeConfig->getValue(
                    \FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_GROUPPROD,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
                ) {
                    $productIds[] = $product->getId();
                } else {
                    $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
                    if (isset($associatedProducts) && is_array($associatedProducts) && count($associatedProducts) > 0) {
                        foreach ($associatedProducts as $associated) {
                            $productIds[] = $associated->getId();
                        }
                    }
                }

                break;
            case 'bundle':
                if ($this->_scopeConfig->getValue(
                    \FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_BUNDLEPROD,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
                ) {
                    $productIds[] = $product->getId();
                } else {
                    $associatedProducts = $product->getTypeInstance(true)->getChildrenIds($product->getId(), false);
                    if (isset($associatedProducts) && is_array($associatedProducts) && count($associatedProducts) > 0) {
                        foreach ($associatedProducts as $associated) {
                            if (is_array($associated))
                                $productIds = array_merge($productIds, array_keys($associated));
                        }
                    }
                }
                break;
            case 'simple':
            case 'virtual':
                $productIds[] = $product->getId();
                break;
            default:
                $productIds[] = $product->getId();
        }

        return implode(",", $productIds);
    }

    /**
     * Returns parameters that should appear in the loader code
     *
     * @return string
     */
    public function getExtraData()
    {
        $coreRegistry = $this->_objectManager->get('\Magento\Framework\Registry');
        $data = [];
        switch ($this->_request->getFullActionName()) {
            // Check for category pages
            case 'catalog_category_view':
                $currentCategory = $coreRegistry->registry('current_category');
                if ($currentCategory instanceof \Magento\Catalog\Model\Category) {
                    $data['CategoryId'] = $currentCategory->getId();
                }
                break;

            // Product Page
            case 'catalog_product_view':
                $currentProduct = $coreRegistry->registry('current_product');
                if ($currentProduct instanceof \Magento\Catalog\Model\Product) {
                    $data['ProductIDs'] = $this->getProductIds($currentProduct);
                    $data['ProductSKU'] = $currentProduct->getSku();
                }
                break;

            default:
                break;
        }

        $customerSession = $this->_objectManager->get('\Magento\Customer\Model\Session');
        $data['CustomerId'] = $customerSession->getCustomerId();
        $data['CartIDs'] = implode(",", $this->productTypeRulesCartProductIds());

        foreach ($data as $key => $value) {
            if (empty($value))
                unset($data[$key]);
        }
        $encoded = \Zend_Json::encode($data);

        $res = <<<SCRIPT

<script type="text/javascript">
    window._4TellBoost = {$encoded};
</script>
<!--4-Tell Recommendations End-->

SCRIPT;
        return $res;
    }

    /**
     * Returns ProductIds in cart, according to product type and config rules
     *
     * @return string
     */
    public function productTypeRulesCartProductIds()
    {
        $checkoutSession = $this->_objectManager->get('\Magento\Checkout\Model\Session');
        $productIds = array();
        $cartItems = $checkoutSession->getQuote()->getAllVisibleItems();
        if ($cartItems) {
            foreach ($cartItems as $cartItem) {
                if (!is_object($cartItem))
                    continue;
                $product = $cartItem->getProduct();
                $productType = $product->getTypeId();
                $productId = $product->getData('entity_id');
                if ($cartItem->getOptionByCode('info_buyRequest')) {
                    $infoBuyRequest = unserialize($cartItem->getOptionByCode('info_buyRequest')->getValue());
                    if (isset($infoBuyRequest['super_product_config']['product_id'])) {
                        $productType = 'grouped';
                    }
                }
                switch ($productType) {
                    case 'configurable':
                        $productIds[] = $productId;
                        break;
                    case 'grouped':
                        if (!$this->_scopeConfig->getValue(
                            \FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_GROUPPROD,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                        ) {
                            $productIds[] = $productId;
                        } else {
                            if (isset($infoBuyRequest['super_product_config']['product_id'])) {
                                $productId = $infoBuyRequest['super_product_config']['product_id'];
                                $productIds[] = $productId;
                            }
                        }

                        break;
                    case 'bundle':
                        if (!$this->_scopeConfig->getValue(
                            \FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_BUNDLEPROD,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                        ) {
                            $productIds[] = $productId;
                        } else {
                            $qty_options = $cartItem->getData('qty_options');
                            $productIds = array_merge($productIds, array_keys($qty_options));
                        }
                        break;
                    case 'simple':
                    case 'virtual':
                        $productIds[] = $productId;
                        break;
                    default:
                        $productIds[] = $productId;
                }
            }
        }
        if (!empty($productIds))
            $productIds = array_unique($productIds);

        return $productIds;
    }
}