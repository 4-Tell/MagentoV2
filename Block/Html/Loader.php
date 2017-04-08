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
     * Catalog product model
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Catalog catgory model
     *
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
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

        $categoryId = (int)$this->getRequest()->getParam('category', false);
        $productId = (int)$this->getRequest()->getParam('id');
        $data = [];

        if ($categoryId) {
            $currentCategory = $this->categoryRepository->get($categoryId);
            if ($currentCategory instanceof \Magento\Catalog\Model\Category) {
                $data['CategoryId'] = $currentCategory->getId();
            }
        }

        // Product Page
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
                throw new NoSuchEntityException();
            }
                $data['ProductIDs'] = $this->getProductIds($product);
                $data['ProductSKU'] = $product->getSku();
        }

        $customerSession = $this->_objectManager->get('\Magento\Customer\Model\Session');
        if ($customerSession->getCustomerId())
            $data['CustomerId'] = $customerSession->getCustomerId();
        else
            $data['CustomerId'] ='';

        $cartProducts = $this->productTypeRulesCartProductIds();
        if(!empty($cartProducts['productIds']))
            $data['CartIDs'] = implode(",", $cartProducts['productIds']);
        else
            $data['CartIDs'] = '';

        if(!empty($cartProducts['productSKUs']))
            $data['CartSKUs'] = implode(",", $cartProducts['productSKUs']);
        else
            $data['CartSKUs']='';

        $res = '';
        foreach ($data as $key => $value) {
            $res .= "window._4TellBoost.$key='$value'; ";
        }
        if (!empty($res))
            $res = '<!--4-Tell Recommendations Start--><script type="text/javascript">' .$res. '</script><!--4-Tell Recommendations End-->';
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
        $productSKUs = array();
        $cartItems = $checkoutSession->getQuote()->getAllVisibleItems();
        if ($cartItems) {
            foreach ($cartItems as $cartItem) {
                if (!is_object($cartItem))
                    continue;
                $product = $cartItem->getProduct();
                $productType = $product->getTypeId();
                $productId = $product->getData('entity_id');
                $productSKUs[$productId] = $product->getData('sku');
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

        $res = array();
        foreach($productIds as $productId){
            $res['productIds'][] = $productId;
            $res['productSKUs'][] = $productSKUs[$productId];
        }

        return $res;
    }
}