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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
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
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productResource = $productResource;
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

        return $productIds;
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
            $productSkus = array();
            $productIds = $this->getProductIds($product);
            $skus = $this->productResource->getProductsSku($productIds);
            foreach($productIds as $id){
                foreach ($skus as $sku){
                    if ($id == $sku['entity_id'])
                        $productSkus[] = $sku['sku'];
                }
            }
            $data['ProductIds'] = implode(",", $productIds);
            $data['ProductSkus'] = implode(",", $productSkus);
        }

        $customerSession = $this->_objectManager->get('\Magento\Customer\Model\Session');
        if ($customerSession->getCustomerId())
            $data['CustomerId'] = $customerSession->getCustomerId();
        else
            $data['CustomerId'] ='';

        $cartData = $this->productTypeRulesCartProductIds();
        foreach($cartData as $key => $val){
            if (empty($val))
                $data[$key] = '';
            else
                $data[$key] = implode(",", $val);
        }

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
        $productSkus = array();
        $parentIds = array();
        $parentSkus = array();

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
                        $parentIds[] = $productId;
                        $qtyOptions = $cartItem->getData('qty_options');
                        if($qtyOptions)
                            $productIds = array_merge($productIds, array_keys($qtyOptions));

                        break;
                    case 'grouped':
                        if (!$this->_scopeConfig->getValue(
                            \FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_GROUPPROD,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                        ) {
                            $productIds[] = $productId;
                            $parentIds[] = $productId;
                        } else {
                            if (isset($infoBuyRequest['super_product_config']['product_id'])) {
                                $productId = $infoBuyRequest['super_product_config']['product_id'];
                                $productIds[] = $productId;
                                $parentIds[] = $productId;
                            }
                        }

                        break;
                    case 'bundle':
                        if ($this->_scopeConfig->getValue(
                            \FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_BUNDLEPROD,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                        ) {
                            $productIds[] = $productId;
                            $parentIds[] = $productId;
                        } else {
                            $qtyOptions = $cartItem->getData('qty_options');
                            $productIds = array_merge($productIds, array_keys($qtyOptions));
                            $parentIds = array_merge($parentIds, array_keys($qtyOptions));
                        }
                        break;
                    case 'simple':
                    case 'virtual':
                        $productIds[] = $productId;
                        $parentIds[] = $productId;
                        break;
                    default:
                        $productIds[] = $productId;
                        $parentIds[] = $productId;
                }
            }
        }
        if (!empty($productIds)) {
            $productIds = array_unique($productIds);
            $skus = $this->productResource->getProductsSku($productIds);
            foreach($productIds as $id){
                foreach ($skus as $sku){
                    if ($id == $sku['entity_id'])
                        $productSkus[] = $sku['sku'];
                }
            }
        }

        if (!empty($parentIds)) {
            $parentIds = array_unique($parentIds);
            $skus = $this->productResource->getProductsSku($parentIds);
            foreach($parentIds as $id){
                foreach ($skus as $sku){
                    if ($id == $sku['entity_id'])
                        $parentSkus[] = $sku['sku'];
                }
            }
        }

        $res = array(
            'CartChildIds' => $productIds,
            'CartParentIds' => $parentIds,
            'CartChildSkus' => $productSkus,
            'CartParentSkus' => $parentSkus,
        );

        return $res;
    }
}