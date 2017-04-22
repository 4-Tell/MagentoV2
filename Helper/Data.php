<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */
namespace FourTell\Recommend\Helper;

use Magento\Framework\App\ResourceConnection;
use FourTell\Recommend\Model\Query;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Class Data
 * @package FourTell\Recommend\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = 'recommend/general_settings/enabled';
    const XML_PATH_CLIENT_ID = 'recommend/general_settings/client_id';
    const XML_PATH_MODE = 'recommend/general_settings/mode';
    const XML_PATH_MANUFACTURER = 'recommend/advanced_settings/manufacturer';
    const XML_PATH_ADVANCED_GROUPPROD = 'recommend/advanced_settings/groupprod';
    const XML_PATH_ADVANCED_BUNDLEPROD = 'recommend/advanced_settings/bundleprod';
    const XML_PATH_ADVANCED_RESTRICT_ATTRIBUTE = 'recommend/advanced_settings/restrict/restrict_attribute';
    const XML_PATH_ADVANCED_RESTRICT_ATTRIBUTE_VAL = 'recommend/advanced_settings/restrict/restrict_attribute';
    const XML_PATH_ADVANCED_RESTRICT_VISIBILITY = 'recommend/advanced_settings/restrict/restrict_visibility_enabled';
    const XML_PATH_ADVANCED_RESTRICT_VISIBILITY_VAL = 'recommend/advanced_settings/restrict/restrict_visibility';
    const XML_PATH_ADVANCED_RESTRICT_STATUS = 'recommend/advanced_settings/restrict/restrict_status_enabled';
    const XML_PATH_ADVANCED_RESTRICT_STATUS_VAL = 'recommend/advanced_settings/restrict/restrict_status';
    const XML_PATH_JS_CODE = 'recommend/display_recommendation/js_loader';
    const XML_PATH_IMAGE_SIZE = 'recommend/display_recommendation/image_size';
    const XML_PATH_IMAGE_THUMB_NUM = 'recommend/display_recommendation/thumbnail_number';
    const XML_PATH_ALTERNATIVE_VIEWS = 'recommend/display_recommendation/alternative_views';
    const XML_PATH_HIDE_RELATED = 'recommend/display_recommendation/hide_magento_related';
    const XML_PATH_HIDE_UPSELL = 'recommend/display_recommendation/hide_magento_upsell';
    const XML_PATH_HIDE_CROSSSEL = 'recommend/display_recommendation/hide_magento_crosssell';
    const FOURTELL_SERVICE_URL_STAGE = 'stage.4-tell.net';
    const FOURTELL_SERVICE_URL_LIVE = 'live.4-tell.net';

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\App\ResourceConnection;
     */
    protected $connection;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\StoresConfig
     */
    protected $_storesConfig;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Lib data collection factory
     *
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /**
     * @var \Magento\Reports\Model\Product\Index\Factory
     */
    protected $_indexFactory;

    /**
     * Product Index Collection
     *
     * @var \Magento\Reports\Model\ResourceModel\Product\Index\Collection\AbstractCollection
     */
    protected $_reportCollection;

    /**
     * Viewed Product Index type
     *
     * @var string
     */
    protected $_indexTypeViewed = \Magento\Reports\Model\Product\Index\Factory::TYPE_VIEWED;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;


    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    protected $resourceModelCatalog;


    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $bundleProductType;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableProduct;


    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $groupedProduct;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoresConfig $storesConfig
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Data\CollectionFactory $dataCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Reports\Model\Product\Index\Factory $indexFactory
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoresConfig $storesConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Data\CollectionFactory $dataCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Reports\Model\Product\Index\Factory $indexFactory,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModelCatalog,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Bundle\Model\Product\Type $bundleProductType,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct
    )
    {
        parent::__construct($context);
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->_storeManager = $storeManager;
        $this->_storesConfig = $storesConfig;
        $this->_resource = $resource;
        $this->_categoryFactory = $categoryFactory;
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->jsonDecoder = $jsonDecoder;
        $this->imageHelper = $imageHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_registry = $registry;
        $this->_indexFactory = $indexFactory;
        $this->_customerVisitor = $customerVisitor;
        $this->_productModel = $productModel;
        $this->resourceModelCatalog = $resourceModelCatalog;
        $this->productResource = $productResource;
        $this->bundleProductType = $bundleProductType;
        $this->configurableProduct = $configurableProduct;
        $this->groupedProduct = $groupedProduct;
    }

    /**
     * Get table name
     * @param string $name
     * @return string
     */
    public function getTableName($name)
    {
        return $this->_resource->getTableName($name);
    }

    /**
     * Returns config value
     *
     * @param string $key
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\App\Config\Element
     */
    public function getConfig($key, $store = null)
    {
        return $this->scopeConfig->getValue(
            $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Memory Limit
     *
     * @return int
     */
    public function getMemoryLimit()
    {
        $memoryLimit = trim(strtoupper(ini_get('memory_limit')));

        if (!isSet($memoryLimit[0])) {
            $memoryLimit = "128M";
        }

        if (substr($memoryLimit, -1) == 'K') {
            return substr($memoryLimit, 0, -1) * 1024;
        }
        if (substr($memoryLimit, -1) == 'M') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024;
        }
        if (substr($memoryLimit, -1) == 'G') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024 * 1024;
        }
        return $memoryLimit;
    }


    /**
     * Get website code
     *
     * @param mixed $website
     * @return string
     */
    protected function _getWebsiteCode($website = null)
    {
        return $this->_storeManager->getWebsite($website)->getCode();
    }

    /**
     * Get Client Aliases
     *
     * @return array
     */
    protected function _getClientAliases()
    {
        $query = new Query($this->_resource);
        $data = $query->getClientAliases();
        return $data;
    }

    /**
     * Map Alias to Stores
     *
     * @param string $clientAlias
     * @return array
     */
    public function map($clientAlias = null)
    {
        if (!$clientAlias) return false;
        $scopes = array();
        $query = new Query($this->_resource);
        $rows = $query->getConfigForClientAliases($clientAlias);
        if ($rows) {
            $needInherited = false;
            $stores = $this->getStores();
            foreach ($rows as $row) {
                if ($row['scope'] == 'stores') {
                    $scopes[] = $row['scope_id'];
                }
            }
            $excludeStoresIds = array();
            // TO DO: NEED TO FIX
            $excludeStores = $query->getClientAliases('stores');
            if ($excludeStores) {
                foreach ($excludeStores as $k => $excludeStore) {
                    $excludeStoresIds[] = $excludeStore['scope_id'];
                }
            }
            foreach ($rows as $row) {
                if ($row['scope'] == 'websites') {
                    foreach ($stores as $store) {
                        if ($store['website_id'] == $row['scope_id'] & !in_array($store['store_id'], $excludeStoresIds)) {
                            $scopes[] = $store['store_id'];
                        }
                    }
                }
            }
            $excludeRowsIds = array();
            foreach ($rows as $row) {
                if ($row['scope'] == 'default') {
                    $needInherited = true;
                    $excludeRowsIds[] = $row['scope_id'];
                }
            }

            //need to know whether on the other websites settings are inherited or not
            if (!empty($stores) && $needInherited) {
                $excludeRows = $query->getClientAliases();
                if ($excludeRows) {
                    foreach ($excludeRows as $k => $exRow) {
                        if ($exRow['scope'] == 'stores') {
                            $excludeRowsIds[] = $exRow['scope_id'];
                        }
                    }
                    foreach ($excludeRows as $k => $exRow) {
                        if ($exRow['scope'] == 'websites') {
                            foreach ($stores as $store) {
                                if ($store['website_id'] == $exRow['scope_id'] & !in_array($store['store_id'], $excludeRowsIds)) {
                                    $excludeRowsIds[] = $store['store_id'];
                                }
                            }
                        }
                    }
                    $storeIds = array();
                    foreach ($stores as $store) {
                        $storeIds[] = $store['store_id'];
                    }
                    $inherited = array_diff($storeIds, $excludeRowsIds);
                    $scopes = array_merge($scopes, $inherited);
                }
            }
        }
        return $scopes;
    }

    /**
     * Get Stores
     *
     * @return \Magento\Store\Model\Website[]
     */
    public function getStores()
    {
        $stores = [];
        foreach ($this->_storeManager->getStores() as $store) {
            $stores[] = array(
                'store_id' => $store->getStoreId(),
                //'group_id' => $store->getGroupId(),
                // 'code' => $store->getCode(),
                'name' => $store->getName(),
                'website_id' => $store->getWebsiteId()
            );
        }
        return $stores;
    }

    /**
     * Checks whether Manufacturer restrictions enabled
     *
     * @param array $storeIds
     * @return bool
     */
    public function isManufacturerEnable($storeIds)
    {
        $manufacturer = $this->_storesConfig->getStoresConfigByPath(self::XML_PATH_MANUFACTURER);
        foreach ($storeIds as $storeId) {
            if (isset($manufacturer[$storeId]) && !empty($manufacturer[$storeId]))
                return true;
        }

        return false;
    }

    /**
     * Get Manufacturer Code from config
     *
     * @param array $storeIds
     * @return string|bool
     */
    public function getManufacturerCode($storeIds)
    {
        $manufacturer = $this->_storesConfig->getStoresConfigByPath(self::XML_PATH_MANUFACTURER);
        foreach ($storeIds as $storeId) {
            if (isset($manufacturer[$storeId]) && !empty($manufacturer[$storeId]))
                return $manufacturer[$storeId];
        }
        return false;
    }

    /**
     * Get categories collection
     *
     * @param array $storeIds
     *
     * @return Collection
     */
    public function getStoresCategories($storeIds)
    {
        /**
         * @var $collection \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
         */
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('image');

        $paths = array();
        foreach ($storeIds as $storeId) {
            $rootCatId = $this->_storeManager->getStore($storeId)->getRootCategoryId();
            $paths[] = "1/$rootCatId/";
        }
        $collection->addPathsFilter($paths);
        return $collection;
    }

    /**
     * productTypeRules
     *
     * @param $call_type
     * @param \Magento\Sales\Model\ResourceModel\Order\Item $orderItem
     * @param $storeIds
     *
     * @return array
     */
    public function productTypeRules($callType, $orderItem, $storeIds = null)
    {
        $order = $orderItem->getOrder();
        $skipRow = false;
        $productId = $orderItem->getData('product_id');
        $productTypeReal = $orderItem->getProduct()->getTypeID();

        switch ($productTypeReal) {
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                if ($callType == 'sales' || $callType == 'tracking')
                    $skipRow = true;
                break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                if (!$this->getConfig(self::XML_PATH_ADVANCED_GROUPPROD, is_null($storeIds) ? $storeIds : $storeIds[0]))
                    $skipRow = true;
                break;
            case \Magento\Bundle\Model\Product\Type::TYPE_CODE:
                if (!$this->getConfig(self::XML_PATH_ADVANCED_BUNDLEPROD, is_null($storeIds) ? $storeIds : $storeIds[0]))
                    $skipRow = true;
                break;
            default:
                if ($orderItem->getData('product_options')) {
                    $productOptions = $orderItem->getData('product_options');

                    $parentIdArray = $this->groupedProduct->getParentIdsByChild($productId);
                    if (isset($parentIdArray[0])) {
                        //if the Simple product is associated with a Grouped product (i.e. child).
                        if ($this->getConfig(self::XML_PATH_ADVANCED_GROUPPROD, is_null($storeIds) ? $storeIds : $storeIds[0])) {
                            if (isset($productOptions['info_buyRequest']['super_product_config']['product_id']))
                                if ($productOptions['info_buyRequest']['super_product_config']['product_id'] != $productId)
                                    $productId = $productOptions['info_buyRequest']['super_product_config']['product_id'];
                        }
                    }

                    $parentIdArray = $this->getBundleParentIdsByChildFixed($productId);
                    if (isset($parentIdArray[0])) {
                        //if the Simple product is associated with a Bundle product (i.e. child).
                        if ($this->getConfig(self::XML_PATH_ADVANCED_BUNDLEPROD, is_null($storeIds) ? $storeIds : $storeIds[0])) {
                            if (isset($productOptions['info_buyRequest']['product']))
                                if ($productOptions['info_buyRequest']['product'] != $productId)
                                    $skipRow = true;
                        }
                    }
                }

            //Simple product is not associated with Configurable, Grouped, Bundle

        }
        if ($skipRow)
            return false;
        $qty = $orderItem->getData('qty_ordered') - ($orderItem->getData('qty_canceled') + $orderItem->getData('qty_refunded'));

        if ($order->getData('status') == 'canceled')
            $qty =0;

        $sku = $this->productResource->getProductsSku(array($productId));

        $res = array(
            'product_id' => $productId,
            'qty' => strval($qty),
            'sku' => $sku[0]['sku']
        );

        return $res;
    }

    /**
     * Getting current scope and scope code id from URL
     *
     * @return array
     */
    public function getCurrentScope()
    {

        $request = $this->_getRequest();
        if ($request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $scopeCode = $this->_storeManager->getStore($request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_STORE))->getCode();
        } elseif ($request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $this->_storeManager->getWebsite($request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE))->getCode();
        } else {
            $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeCode = 0;
        }
        return [$scopeType, $scopeCode];
    }

    /**
     * Create product image cache
     *
     * @param $product
     *
     * @return void
     */
    public function createImageCache($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeIds = $product->getStoreIds();
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $alternativeImages = [];
        foreach ($storeIds as $storeId) {
            $product = $objectManager->create('\Magento\Catalog\Model\Product')->setStoreId($storeId)->load($product->getId());
            $alternativeViews = $this->getAlternativeViews($storeId);
            if (!empty($alternativeViews))
                $alternativeViews = explode(',',$alternativeViews);
            if (!empty($this->getThumbnailNumber($storeId)))
                $alternativeViews[] = $this->getThumbnailNumber($storeId);
            if (empty($alternativeViews))
                continue;
            $imageSize = $this->getImageSize($storeId);
            foreach($alternativeViews as $thumbnail_number) {
                $imageFile = '';
                if ((int)$thumbnail_number > 0 and (int)$thumbnail_number < 21) {
                    $mediaGallery = $product->getMediaGalleryImages();
                    if ($mediaGallery instanceof \Magento\Framework\Data\Collection) {
                        foreach ($mediaGallery as $image) {
                            if (($image->getPosition() == $thumbnail_number) && ($image->getMediaType() == 'image')) {
                                $imageFile = $image->getFile();
                                break;
                            }
                        }
                    }
                } else {
                    $imageFile = $product->getData($thumbnail_number);
                }
                if (!empty($imageFile))
                    $alternativeImages[$storeId][$thumbnail_number] =
                        $this->imageHelper->init($product, 'recommend_product_image_listing', $imageSize)
                        ->setImageFile($imageFile)
                        ->getUrl();
            }
        }
        return $alternativeImages;
    }

    /**
     * Get Image Url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param $storeId
     * @return string
     */
    public function getImageUrlByPos($product, $storeId, $pos)
    {
        $imageSize = $this->getImageSize($storeId);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('\Magento\Catalog\Model\Product')->setStoreId($storeId)->load($product->getId());
        $mediaGallery = $product->getMediaGalleryImages();
        if ($mediaGallery instanceof \Magento\Framework\Data\Collection) {
            foreach ($mediaGallery as $image) {
                if (($image->getPosition() == $pos) && ($image->getMediaType() == 'image')) {
                    $imageFile = $image->getFile();
                    $url = $this->imageHelper->init($product, 'recommend_product_image_listing', $imageSize)
                        ->setImageFile($imageFile)
                        ->getUrl();
                    return $url;
                }
            }
        }
        //pos = attribute
        $imageFileAlternative = $product->getData($pos);
        if (!empty($imageFileAlternative)) {
            $url = $this->imageHelper->init($product, 'recommend_product_image_listing', $imageSize)
                ->setImageFile($imageFileAlternative)
                ->getUrl();
            return $url;
        }
        return '';
    }

    /**
     *
     * Attribute Restrict
     *
     * @param $storeId
     * @return array
     */
    public function getAttributeRestrict($storeId)
    {
        if ($this->getConfig(self::XML_PATH_ADVANCED_RESTRICT_ATTRIBUTE, $storeId)) {
            $restrictVal = $this->getConfig(self::XML_PATH_ADVANCED_RESTRICT_ATTRIBUTE_VAL, $storeId);
            $restrict = explode(',', $restrictVal);
            if (isset($restrict[0]) && !empty($restrict[0]))
                return $restrict;
        }
        return [];
    }

    /**
     * Visibility Restrict
     *
     * @param $storeId
     * @return array
     */
    public function getVisibilityRestrict($storeId)
    {
        if ($this->getConfig(self::XML_PATH_ADVANCED_RESTRICT_VISIBILITY, $storeId)) {
            $restrictVal = $this->getConfig(self::XML_PATH_ADVANCED_RESTRICT_VISIBILITY_VAL, $storeId);
            $restrict = explode(',', $restrictVal);
            if (isset($restrict[0]) && !empty($restrict[0]))
                return $restrict;
        }
        return [];
    }

    /**
     *
     * Status Restrict
     * @param $storeId
     * @return array
     */
    public function getStatusRestrict($storeId)
    {
        if ($this->getConfig(self::XML_PATH_ADVANCED_RESTRICT_STATUS, $storeId)) {
            $restrictVal = $this->getConfig(self::XML_PATH_ADVANCED_RESTRICT_STATUS_VAL, $storeId);
            $restrict = explode(',', $restrictVal);
            if (isset($restrict[0]) && !empty($restrict[0]))
                return $restrict;
        }
        return [];
    }

    /**
     *
     * Thumbnail Number
     *
     * @param $storeId
     * @return int
     */
    public function getThumbnailNumber($storeId)
    {
        return $this->getConfig(self::XML_PATH_IMAGE_THUMB_NUM, $storeId);
    }

    /**
     *
     * Alternative Views
     *
     * @param $storeId
     * @return int
     */
    public function getAlternativeViews($storeId)
    {
        return $this->getConfig(self::XML_PATH_ALTERNATIVE_VIEWS, $storeId);
    }

    /**
     *
     *  Image Size
     *
     * @param $storeId
     * @return array
     */
    public function getImageSize($storeId)
    {
        $attributes = [];
        if ($size = $this->getConfig(self::XML_PATH_IMAGE_SIZE, $storeId)) {
            $size = explode(',', $size);

            if (isset($size[0]) && !empty($size[0]))
                $attributes['width'] = $size[0];

            if (isset($size[1]) && !empty($size[1]))
                $attributes['height'] = $size[1];
        }

        return $attributes;
    }

    /**
     * Get mode (live or test)
     *
     * @return string
     */
    public function getMode()
    {
        if ($this->_storeManager->getStore()->isCurrentlySecure())
            $protocol = 'https://';
        else
            $protocol = 'http://';
        if ($this->getConfig(self::XML_PATH_MODE, $this->_storeManager->getStore()->getId()) == \FourTell\Recommend\Model\Config\Source\Mode::STAGE) {
            return $protocol . self::FOURTELL_SERVICE_URL_STAGE;
        }
        return $protocol . self::FOURTELL_SERVICE_URL_LIVE;
    }

    /**
     * Get the Client ID from the system configuration
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->getConfig(self::XML_PATH_CLIENT_ID, $this->_storeManager->getStore()->getId());
    }

    /**
     * Checks whether module Enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->getConfig(self::XML_PATH_ENABLED, $this->_storeManager->getStore()->getId()) != "1")
            return false;
        //client_id empty it is same that modele disabled
        if ($this->getConfig(self::XML_PATH_CLIENT_ID, $this->_storeManager->getStore()->getId()) == "")
            return false;

        return true;
    }

    /**
     * Get locale timezone
     *
     * @param array $storeIds
     * @return array
     */
    public function getTimezone($storeIds)
    {
        return $this->_storesConfig->getStoresConfigByPath(\Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_GENERAL_LOCALE_TIMEZONE);
    }

    /**
     * Retrieve array of related bundle product ids by selection product id(s)
     *
     * @param int|array $childId
     * @return array
     */
    public function getBundleParentIdsByChildFixed($childId)
    {
        $query = new Query($this->_resource);
        return $query->getParentIdsByChild($childId);
    }

    /**
     * Returns Grouped product max price
     *
     * @return Product
     */
    public function getGroupedMaxPrice($product)
    {
        $maxPrice = null;
        if ($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $products = $product->getTypeInstance()->getAssociatedProducts($product);
            $maxPrice = 0;
            foreach ($products as $item) {
                $_product = clone $item;
                $_product->setQty(\Magento\Framework\Pricing\PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT);
                $price = $_product->getPriceInfo()
                    ->getPrice(FinalPrice::PRICE_CODE)
                    ->getValue();
                if ($price !== false) {
                    $maxPrice += $price;
                }
            }
        }
        return $maxPrice;
    }
}