<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Model;

use FourTell\Recommend\Api\FeedInterface;
use FourTell\Recommend\Helper\Data as FourTellHelper;
use Magento\Bundle\Model\Product\Type;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use \DateTime;
use \stdClass;

/**
 * class Feed
 */
class Feed implements FeedInterface
{
    const MODULE_NAME = 'FourTell_Recommend';

    /**
     * Recommend helper
     *
     * @var \FourTell\Recommend\Helper\Data
     */
    protected $_helper;

    /**
     * Array of items
     *
     * @var array
     */
    protected $_result;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * friendly search criteria list
     *
     * @var array
     */
    protected $_searchCriteria = [];

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attrCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $_orderItemCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $_customersFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /*
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $_customerMetadataService;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * Customer Group
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $_customerGroup;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $_resourceCategory;

    /**
     * @param \FourTell\Recommend\Helper\Api $helper
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param \Psr\Log\LoggerInterface $logger
     * @param Csv $csv
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customersFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface
     * @param \Magento\Customer\Api\CustomerMetadataInterface $customerMetadataService
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     * @param array $data
     */
    public function __construct(
        \FourTell\Recommend\Helper\Api $helper,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        \Psr\Log\LoggerInterface $logger,
        Csv $csv,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customersFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadataService,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Catalog\Model\ResourceModel\Category $resourceCategory,

        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->csv = $csv;
        $this->productMetadata = $productMetadata;
        $this->_customerViewHelper = $customerViewHelper;
        $this->attrCollectionFactory = $attrCollectionFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->_customersFactory = $customersFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->date = $date;
        $this->stockRegistry = $stockRegistry;
        $this->_addressRepository = $addressRepository;
        $this->_logger = $logger;
        $this->attributeRepository = $attributeRepositoryInterface;
        $this->_customerMetadataService = $customerMetadataService;
        $this->productResource = $productResource;
        $this->_customerGroup = $customerGroup;
        $this->_resourceCategory = $resourceCategory;
    }


    public function getCustomers()
    {
        $this->_result = [];
        $this->resultDataHead = ['CustomerID', 'Email', 'Group', 'Name', 'Address', 'Address2', 'City', 'State', 'PostalCode', 'Country', 'Phone', 'DoNotTrack'];
        $searchResultDataHead = array_map('strtolower', $this->resultDataHead);
        $extraFields = $this->_helper->getExtraFields();
        if (!empty($extraFields)) {
            foreach ($extraFields as $key => $extraField) {
                $extraField = strtolower($extraField);
                if (!in_array($extraField, $searchResultDataHead))
                    $this->resultDataHead[] = $extraField;
                else
                    unset($extraFields[$key]);
            }
        }
        $this->_result[] = $this->resultDataHead;
        $customerCollection = $this->_customersFactory->create();
        $customerCollection->addAttributeToSelect(FourTellHelper::FOURTELL_DO_NOT_TRACK_CUSTOMER);
        $clientAlias = $this->_helper->ClientAlias;
        $storeIds = $this->_helper->map($clientAlias);

        //DateRange
        $dateRange = $this->_helper->getDateRange();
        if ($dateRange) {
            $filterDateRange = ['from' => $dateRange[0]];

            if (isset($dateRange[1])) {
                $plusOneDay = $this->_helper->plusOneDay($dateRange[1], $format = 'Y-m-d');
                $filterDateRange['to'] = $plusOneDay;
            }

            $customerCollection->getSelectSql()->joinLeft(
                ['so' => $this->_helper->getTableName('sales_order')],
                'so.customer_id = e.entity_id',
                ['so.created_at']
            )->group(
                'so.customer_id'
            );

            $whereCreatedAt = "e.created_at >= '" . $filterDateRange['from'] . "'";
            $whereUpdatedAt = "e.updated_at >= '" . $filterDateRange['from'] . "'";
            $whereSOCreatedAt = "so.created_at >= '" . $filterDateRange['from'] . "'";
            if (isset($filterDateRange['to'])) {
                $whereCreatedAt .= " && e.created_at <= '" . $filterDateRange['to'] . "'";
                $whereUpdatedAt .= " && e.updated_at <= '" . $filterDateRange['to'] . "'";
                $whereSOCreatedAt .= " && so.created_at <= '" . $filterDateRange['to'] . "'";
            }

            $customerCollection->getSelect()
                ->where("($whereCreatedAt) || ($whereUpdatedAt) || $whereSOCreatedAt");
        }
        $customerCollection->addFieldToFilter('store_id', ['in' => $storeIds]);

        if ($this->_helper->ResultType == 'Count') {
            $customerCount = new stdClass();
            $customerCount->rows = $customerCollection->getSize();
            return $customerCount;
        }

        $items = $customerCollection->getItems();

        $customerGroups = $this->_customerGroup->toOptionArray();
        $groups = [];
        foreach($customerGroups as $customerGroup){
            $groups[$customerGroup['value']] = $customerGroup['label'];
        }
        foreach ($items as $item) {
            $group = (isset($groups[$item->getGroupId()])) ? $groups[$item->getGroupId()] : '';
            if($item->getData(FourTellHelper::FOURTELL_DO_NOT_TRACK_CUSTOMER)){
                $this->_result[] = [
                    $item->getId(), '', $group, '', '', '', '', '', '', '', '', 'True'
                ];
                continue;
            }
            $res = [
                $item->getId(),
                $item->getEmail(),
                $group,
                $this->getCustomerName($item)
            ];

            if ($item->getDefaultBilling()) {
                try {
                    $customerAddress = $this->_addressRepository->getById($item->getDefaultBilling());
                    $street = $customerAddress->getStreet();
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // Swallow this. The DB is dirty: address specified in customer_entity is not in customer_address_entity
                    $this->_logger->info("4-Tell: Customer address specified in the customer_entity table is not in the customer_address_entity table for customer id ".$item->getId());
                }
                if (!is_null($street)) {
                    $res[] = $street[0];
                    if (isset($street[1]))
                        $res[] = $street[1];
                    else
                        $res[] = '';
                } else {
                    $res[] = '';
                    $res[] = '';
                }
                $res[] = $customerAddress->getCity();
                $res[] = $customerAddress->getRegion()->getRegion();
                $res[] = $customerAddress->getPostcode();
                $res[] = $customerAddress->getCountryId();
                $res[] = $customerAddress->getTelephone();
            } else {
                //'Address', 'Address2', 'City', 'State', 'PostalCode', 'Country', 'Phone'
                for ($ii = 0; $ii < 7; $ii++) {
                    $res[] = '';
                }
                foreach ($extraFields as $extraField) {
                    $res[] = '';
                }
            }
            $res[] = 'False';
            foreach ($res as $key => $value) {
                if (is_null($value))
                    $res[$key] = '';
            }

            $this->_result[] = $res;
        }

        return $this->_result;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductTypeInterface[]
     */
    public function getProductTypes()
    {
        return $this->productTypeList->getProductTypes();
    }

    /**
     *
     * Get the products Inventory
     *
     */
    public function getInventory()
    {
        return $this->getCatalog();
    }





    /**
     *
     * Get the products data
     *
     */
    public function getCatalog()
    {
        switch ($this->_helper->getFeedMethod()) {
            case 'getInventory':
                $this->resultDataHead = ['ProductID', 'Inventory'];
                break;

            default:
                $this->resultDataHead = ['SKU', 'ParentSKU', 'InternalID', 'ParentID', 'Name', 'CategoryIDs',
                    'ManufacturerID', 'Price', 'SalePrice', 'PromotionPrice', 'ListPrice', 'Cost', 'Inventory', 'Visible', 'Link', 'ImageLink', 'AltViewImageLinks', 'Ratings',
                    'ProductType', 'Visibility', 'Active', 'StockAvailability', 'ActivatedDate', 'ModifiedDate'];
                break;
        }
        // Create file header
        $this->resultData = [];
        $searchResultDataHead = array_map('strtolower', $this->resultDataHead);
        $extraFields = $this->_helper->getExtraFields();
        if (!is_null($extraFields)) {
            foreach ($extraFields as $key => $extraField) {
                if (!in_array(strtolower($extraField), $searchResultDataHead))
                    $this->resultDataHead[] = $extraField;
                else
                    unset($extraFields[$key]);
            }
        }
        $this->resultData[] = $this->resultDataHead;
        $clientAlias = $this->_helper->ClientAlias;
        $storeIds = $this->_helper->map($clientAlias);

        if (empty($storeIds))
            return $this->resultData;

        $collection = $this->_productFactory->create()->getCollection();
        // restrict collection
        $trueProductIds = [];
        foreach ($storeIds as $storeId) {
            $restrictCollection = $this->_productFactory->create()->getCollection();
            $restrictCollection->addStoreFilter($storeId);
            $attributeRestrict = $this->_helper->getAttributeRestrict($storeId);
            if (!empty($attributeRestrict)) {
                if (strcasecmp($attributeRestrict[0], 'category') == 0) {
                    $categoryCollection = $this->_helper->getStoresCategories($storeIds);
                    $paths = [];
                    $catRestrictIds = explode(',', $attributeRestrict[1]);
                    foreach ($catRestrictIds as $catRestrictId) {
                        $paths[] = '%/' . $catRestrictId . '/';
                    }
                    $categoryCollection->addPathsFilter($paths);
                    $catRestrictIds = array_merge($catRestrictIds, $categoryCollection->getAllIds());
                    $restrictCollection->addCategoriesFilter(array('in' => $catRestrictIds));
                } else {
                    $restrictCollection->addAttributeToFilter($attributeRestrict[0], $attributeRestrict[1]);
                }
            }
            $visibilityRestrict = $this->_helper->getVisibilityRestrict($storeId);
            if (!empty($visibilityRestrict))
                $restrictCollection->setVisibility($visibilityRestrict);

            $statusRestrict = $this->_helper->getStatusRestrict($storeId);
            if (!empty($statusRestrict))
                $restrictCollection->addAttributeToFilter('status', ['in' => $statusRestrict]);
            $trueProductIds = array_merge($trueProductIds, $restrictCollection->getAllIds());

        }
        $collection->addIdFilter($trueProductIds);
        if ($this->_helper->isManufacturerEnable($storeIds)) {
            $manufacturerCode = $this->_helper->getManufacturerCode($storeIds);
            if (!empty($manufacturerCode)) {
                try {
                    $entityTypeCode = 'catalog_product';
                    $this->attributeRepository->get($entityTypeCode, $manufacturerCode)->getFrontendLabels();
                    $collection->addAttributeToSelect($manufacturerCode, 'left');
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    //  attribute is not exists
                }
            }

        }
        $collection->addAttributeToSelect('*');

        //DateRange
        $dateRange = $this->_helper->getDateRange();

        if ($dateRange) {
            $filterDateRange = [
                'from' => $dateRange[0],
                'date' => true
            ];
            if (isset($dateRange[1])) {
                $plusOneDay = $this->_helper->plusOneDay($dateRange[1], $format = 'Y-m-d');
                $filterDateRange['to'] = $plusOneDay;
            }
            $collection->addFieldToFilter('updated_at', $filterDateRange);
        }

        //ResultType - Count
        //just a count of how many rows of data would be provided for the given set of query parameters
        if ($this->_helper->ResultType == 'Count') {
            $productCount = new stdClass();
            $productCount->rows = $collection->getSize();
            //Retrieve count of collection loaded items
            //$productCount->rows = $collection->count();
            return $productCount;
        }

        $rowRange = $this->_helper->getRowRange();
        if (isset($rowRange[0]) && isset($rowRange[1]))
            $collection->getSelect()->limit((int)$rowRange[1], (int)$rowRange[0]);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configurableProductModel = $objectManager->get('\Magento\ConfigurableProduct\Model\Product\Type\Configurable');
        $groupedProductModel = $objectManager->get('\Magento\GroupedProduct\Model\Product\Type\Grouped');
        $bundleProductModel = $objectManager->get('\Magento\Bundle\Model\Product\Type');
        $bundleResource = $objectManager->get('\Magento\Bundle\Model\ResourceModel\Selection');

        //addIsInStockFilterToCollection
        $stockFlag = 'has_stock_status_filter';
        $collection->setFlag($stockFlag, true);
        //$collection->printLogQuery(true,true);
        if ($this->_helper->getFeedMethod() == 'getInventory') {
            foreach ($collection as $product) {
                $productId = $product->getEntityId();
                $manageStock = $this->stockRegistry->getStockItem($product->getId())->getManageStock();
                if ($manageStock)
                    $qty = FourTellHelper::number_format_default_to_zero($this->getProductStockQty($product), 0);
                else
                    $qty = '';

                //Set the inventory to zero for those products that are "Disabled
                $status = $product->getStatus();
                if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)
                    $qty = 0;

                $this->resultData[] = [$productId, $qty];
            }
            return $this->resultData;
        }
        foreach ($collection as $product) {
            try {
                $productSku = '';
                $parentSku = '';
                $productId = '';
                $parentId = '';
                $avg = '';
                $cat = '';
                $manufacturerValue = '';
                $price = '';
                $specialPrice = '';
                $listPrice = '';
                $productUrl = '';
                $image = '';
                $alternativeImages = [];
                $visibilityValue = '';
                $stockAvailability = '';
                $activatedAt = '';
                $modifiedAt = '';
                $parentIds = [];
                $parentSkus = [];
                $extraFieldsValue = [];
                $extraFieldsSwatch = [];
                $storesCode = [];
                $productId = $product->getEntityId();
                $productSku = $product->getSku();

                if (empty($productSku))
                {
                    $this->_logger->critical("Skipping productId ".$productId." because it has no SKU");
                    // Append an error row to the resultData product array. 
                    $this->resultData[] = ["ERROR-".$productId, null, $productId, null, $product->getName()." - ERROR: No SKU"];
                    continue;                    
                }
                
                $cat = implode(",", $product->getCategoryIds());
                $manufacturerValue = '';
                if (!empty($manufacturerCode)) {
                    $manufacturerValue = $product->getData($manufacturerCode);
                    if (is_null($manufacturerValue))
                        $manufacturerValue = '';
                }

                $qty = $this->getProductStockQty($product);
                $productUrl = $product->getUrlModel()->getUrl($product);
                if (is_null($productUrl))
                    $productUrl = '';
                $productUrl = $this->_helper->fixLink($productUrl);

                $images = $this->_helper->createImageCache($product);
                foreach ($images as $key => $value)
                    $images[$key] = $this->_helper->fixLink($value);

                $thumbnailNumber = $this->_helper->getThumbnailNumber($storeIds[0]);
                foreach ($storeIds as $storeId){
                    if (isset($images[$storeId][$thumbnailNumber])) {
                        $image = $images[$storeId][$thumbnailNumber];
                        break;
                    }
                }
                if (!empty($image)){
                    foreach ($storeIds as $storeId){
                        if (isset($images[$storeId][$thumbnailNumber])) {
                            unset($images[$storeId][$thumbnailNumber]);
                        }
                    }
                }

                $alternativeImages = [];
                foreach ($storeIds as $storeId){
                    if (isset($images[$storeId]))
                        $alternativeImages = array_merge($alternativeImages, $images[$storeId]);
                }
                if (!empty($alternativeImages)) {
                    $alternativeImages = array_unique($alternativeImages);
                    $alternativeImages = implode(',',$alternativeImages);
                }
                else
                    $alternativeImages = '';

                $visibility = $product->getVisibility();
                $visibilityValue = \Magento\Catalog\Model\Product\Visibility::getOptionText($visibility);
                if(is_null($visibilityValue))
                    $visibilityValue = '';

                $visible = 1;
                //Not Visible Individually
                if ($visibility == 1)
                    $visible = 0;
                $statusFlag = 1;
                $status = $product->getStatus();
                if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
                    $qty = 0;
                    $statusFlag = 0;
                }

                if ($this->stockRegistry->getProductStockStatus($product->getId())) {
                    $stockAvailability = 'In Stock';
                } else {
                    $stockAvailability = 'Out of Stock';
                    //$qty = 0;
                }

                $priceList = $product->getData('msrp');
                $priceCost = $product->getData('cost');
                if ($specialPrice = $product->getSpecialPrice()) {
                    $specialPrice = str_replace(",", "", FourTellHelper::number_format_default_to_zero($specialPrice, 2));
                    $now = $this->date->timestamp();
                    if ($specialFromDate = $product->getSpecialFromDate()) {
                        if ($now < $this->date->timestamp($specialFromDate)) {
                            $specialPrice = '';
                        }
                    }
                    if ($specialToDate = $product->getSpecialToDate()) {
                        if ($now > $this->date->timestamp($specialToDate)) {
                            $specialPrice = '';
                        }
                    }
                } else {
                    $specialPrice = '';
                }

                if ($product->getTypeId() == "simple" || $product->getTypeId() == "virtual") {
                    // No Grouped/Bundled Parent IDs in Catalog Feed for Simple Products if simple not visible individually
                    // 1 Not Visible Individually
    //                if ($product->getVisibility() == 1) {
    //                    $parentIdArray = $groupedProductModel->getParentIdsByChild($productId);
    //                    if (isset($parentIdArray[0])) {
    //                        $parentIds = array_merge($parentIds, $parentIdArray);
    //                    }
    //                    // bundle fixed issue
    //                    $parentIdArray = $this->_helper->getBundleParentIdsByChildFixed($productId);
    //                    if (isset($parentIdArray[0])) {
    //                        $parentIds = array_merge($parentIds, $parentIdArray);
    //                    }
    //                }
                    $parentIdArray = $configurableProductModel->getParentIdsByChild($productId);
                    if (isset($parentIdArray[0])) {
                        $parentIds = array_merge($parentIds, $parentIdArray);
                    }
                }
                if (isset($parentIds[0])) {
                    $skus = $this->productResource->getProductsSku($parentIds);
                    foreach ($skus as $sku) {
                        $parentSkus[] = $sku['sku'];
                    }
                    $parentId = implode(',', $parentIds);
                    $parentSku = implode(',', $parentSkus);
                }
                else {
                    $parentId = '';
                    $parentSku = '';
                }

                if (!$summaryData = $product->getRatingSummary()) {
                    $this->_reviewFactory->create()->getEntitySummary($product, $storeIds[0]);
                }
                $summaryData = $product->getRatingSummary();
                $ratingPercent = $summaryData->getRatingSummary();
                //5 star
                if ($ratingPercent)
                    $avg = round($ratingPercent * 5 / 100);

                if (!is_null($extraFields)) {
                    
                    foreach ($extraFields as $extraField) {
                        $extraFieldsSwatch[] = str_replace('.swatch', '', $extraField);
                    }

                    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributes */
                    $attributes = $this->attrCollectionFactory->create()
                        ->addFieldToFilter('attribute_code', ['in' => $extraFieldsSwatch])
                        ->load();

                    $storesDetail = $this->_helper->getStores();
                    foreach($storesDetail as $storeDetail) {
                        if (in_array($storeDetail['store_id'],$storeIds))
                            $storesCode[] = $storeDetail['code'];
                    }

                    foreach ($extraFields as $extraField) {
                        $extraField = strtolower($extraField);
                        $found = false;
                        $extraFieldExploded = explode('.', $extraField);
                        switch ($extraField) {
                            case 'maxprice':
                                $maxPrice = null;
                                if ($product->getTypeId() == Type::TYPE_CODE ){
                                    $finalPrice = $product->getPriceInfo()->getPrice('final_price');
                                    $maxPrice = $finalPrice->getMaximalPrice()->getValue();
                                }
                                if ($product->getTypeId() == Grouped::TYPE_CODE){
                                    $maxPrice = $this->_helper->getGroupedMaxPrice($product);
                                }
                                $extraFieldsValue[] = (!is_null($maxPrice)) ? FourTellHelper::number_format_default_to_zero($maxPrice, 2, '.', '') : '';
                                $found = true;
                                break;
                            case 'related':
                                $extraFieldsValue[] = implode(',', $product->getRelatedProductIds());
                                $found = true;
                                break;
                            case 'up-sells':
                                $extraFieldsValue[] = implode(',', $product->getUpSellProductIds());
                                $found = true;
                                break;
                            case 'cross-sells':
                                $extraFieldsValue[] = implode(',', $product->getCrossSellProductIds());
                                $found = true;
                                break;
                            case (strpos($extraField, 'image.') !== false):
                                $imagePos = $extraFieldExploded;
                                $extraFieldsValue[] = $this->_helper->getImageUrlByPos($product, $storeIds[0], $imagePos[1]);
                                $found = true;
                                break;

                            case (in_array(array_shift($extraFieldExploded), $storesCode)):
                                $viewScopeParam = $extraFieldExploded;
                                foreach($storesDetail as $storeDetail){
                                    if ($storeDetail['code'] == $viewScopeParam[0]){
                                        $found = true;
                                        if ($viewScopeParam[1] == 'url_key_4tell') {
                                            $value = $this->_helper->getProductUrlInStore($productId, $storeDetail['store_id']);
                                            $value = $this->_helper->fixLink($value);
                                        }
                                        else
                                            $value = $this->productResource->getAttributeRawValue($product->getEntityId(), $viewScopeParam[1], $storeDetail['store_id']);
                                            $extraFieldsValue[] = ($value) ? $value : "";
                                        break;
                                    }
                                }

                            default:
                                foreach ($attributes as $attribute) {
                                    $swatchField = $attribute->getAttributeCode() . '.swatch';
                                    if ($swatchField == $extraField) {
                                        // check for swatch
                                        $optionIdvalue = $product->getData($attribute->getAttributeCode());
                                        $swatchHelper = $objectManager->get("Magento\Swatches\Helper\Media");
                                        $swatchCollection = $objectManager->create('Magento\Swatches\Model\ResourceModel\Swatch\Collection');
                                        $swatchCollection->addFieldtoFilter('option_id', $optionIdvalue);
                                        $resultItem = $swatchCollection->getFirstItem();
                                        if ($resultItem['type'] == \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_IMAGE && !empty($resultItem['value'])) {
                                            $swatchImage = $swatchHelper->getSwatchAttributeImage(
                                                \Magento\Swatches\Model\Swatch::SWATCH_IMAGE_NAME,
                                                $resultItem['value']
                                            );
                                        } else
                                            $swatchImage = $resultItem['value'];

                                        if (!is_null($swatchImage))
                                            $extraFieldsValue[] = $swatchImage;
                                        else
                                            $extraFieldsValue[] = '';

                                        $found = true;
                                        break;
                                    }
                                    if ($attribute->getAttributeCode() == $extraField) {
                                        if ($attribute->getFrontendInput() == 'multiselect' || $attribute->getFrontendInput() == 'select') {
                                            $getExtraFieldValue = [];
                                            $extraOptionIds = $product->getData($extraField);
                                            $extraOptionIds = explode(',', $extraOptionIds);
                                            $extraOptions = $this->attrOptionCollectionFactory->create()
                                                ->setAttributeFilter($attribute->getAttributeId())
                                                ->setStoreFilter($storeIds[0])
                                                ->setPositionOrder('asc', true)->load();
                                            $extraOptions = $extraOptions->toArray();
                                            if ($extraOptions['totalRecords']) {
                                                foreach ($extraOptions['items'] as $extraOption) {
                                                    if (in_array($extraOption['option_id'], $extraOptionIds)) {
                                                        $getExtraFieldValue[] = $extraOption['value'];
                                                    }
                                                }
                                            }
                                            if (!empty($getExtraFieldValue)) {
                                                $extraFieldsValue[] = implode(',', $getExtraFieldValue);
                                            } else
                                                $extraFieldsValue[] = "";
                                        } else {
                                            if (!is_null($product->getData($extraField)))
                                                $extraFieldsValue[] = $product->getData($extraField);
                                            else
                                                $extraFieldsValue[] = '';
                                        }
                                        $found = true;
                                        break;
                                    }
                                }
                        }
                        if (!$found)
                            $extraFieldsValue[] = "";
                    }
                }

                $price = $product->getPrice();

                if ($product->getTypeId() == Type::TYPE_CODE ){
                    $finalPrice = $product->getPriceInfo()->getPrice('final_price');
                    $price = $finalPrice->getMinimalPrice()->getValue();
    //                $_maximalPrice = $bundleObj->getMaximalPrice()->getValue();
                }
                if ($product->getTypeId() == Grouped::TYPE_CODE){
                    $finalPrice = $product->getPriceInfo()->getPrice('final_price');
                    $price = $finalPrice->getMinimalPrice()->getValue();
                    //$maxPrice = $this->_helper->getGroupedMaxPrice($product);
                }


                $zones = $this->_helper->getTimezone($storeIds);
                $zone = $zones[$storeIds[0]];
                $activatedAt = new \DateTime($product->getData('created_at'), new \DateTimeZone($zone));
                $modifiedAt = new \DateTime($product->getData('updated_at'), new \DateTimeZone($zone));
                //fix issue for v.2.1.0
                try {
                    $listPrice = FourTellHelper::number_format_default_to_zero($product->getFinalPrice(), 2, '.', '');
                } catch (\Exception $e) {
                    $listPrice = '';
                }

                $resultData = [
                    $productSku,
                    $parentSku,
                    $productId,
                    $parentId,
                    $product->getName(),
                    $cat,
                    $manufacturerValue,
                    FourTellHelper::number_format_default_to_zero($price, 2, '.', ''),
                    $specialPrice,
                    $listPrice,
                    FourTellHelper::number_format_default_to_zero($priceList, 2, '.', ''),
                    FourTellHelper::number_format_default_to_zero($priceCost, 2, '.', ''),
                    FourTellHelper::number_format_default_to_zero($qty, 0),
                    (string)$visible,
                    $productUrl,
                    $image,
                    $alternativeImages,
                    (string)$avg,
                    $product->getTypeId(),
                    $visibilityValue,
                    (string)$statusFlag,
                    $stockAvailability,
                    $activatedAt->format('Y-m-d H:i:sP'),
                    $modifiedAt->format('Y-m-d H:i:sP')
                ];

                foreach ($extraFieldsValue as $extraFieldValue) {
                    $resultData[] = $extraFieldValue;
                }

                $this->resultData[] = $resultData;
            } catch (\Exception $e) {
                $this->_logger->critical("Exception while parsing productId ".$productId);
                $this->_logger->critical($e);
                // Append an error row to the resultData product array. 
                $this->resultData[] = ["ERROR-".$productId, null, $productId, null, $product->getName()." - ERROR: ".$e];
                continue;                    
            }
        }

        return $this->resultData;
    }

    /**
     * Return version
     *
     * @api
     * @return version.
     */
    public function getVersion()
    {
        $result = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $objectManager->get('\Magento\Framework\Module\ModuleListInterface')->getOne(self::MODULE_NAME)['setup_version'];
        $result[] = [
            'extension' => $version,
            'magento' => $this->productMetadata->getName() . '/' . $this->productMetadata->getVersion() . ' (' . $this->productMetadata->getEdition() . ')',
            'memLimit' => $this->_helper->getMemoryLimit(),
            'OSType' => php_uname($mode = "s"),
            'OSVersion' => php_uname($mode = "v"),
            'maxExecutionTime' => ini_get("max_execution_time")
        ];

        return $result;
    }

    /**
     *
     * Get the Manufacturers data
     *
     */
    function getManufacturerNames()
    {
        $result = [];
        $result[] = ['ID', 'Name'];
        $clientAlias = $this->_helper->ClientAlias;
        $storeIds = $this->_helper->map($clientAlias);
        if (empty($storeIds))
            return $result;

        if ($this->_helper->isManufacturerEnable($storeIds)) {
            $manufacturerCode = $this->_helper->getManufacturerCode($storeIds);
            if (!empty($manufacturer_code)) {
                /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $options */
                $result = array_merge($result, $this->getOption($manufacturerCode));
            }
        }
        return $result;
    }

    /**
     * Get attribute options by attribute code
     *
     * @param string $attributeCode
     * @return array
     */
    protected function getOption($attributeCode)
    {
        $result = [];
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $collection */
        $collection = $this->attrCollectionFactory->create()
            ->addFieldToSelect(['attribute_code', 'attribute_id'])
            ->addFieldToFilter('attribute_code', $attributeCode)
            ->setFrontendInputTypeFilter(['in' => ['select', 'multiselect']])
            //->setAttributeFilter($attributeId)
            //->setPositionOrder('asc', true)
            ->load();
        foreach ($collection as $item) {
            $options = $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($item->getAttributeId())
                ->setStoreFilter(1)// For get option value for specific store
                ->setPositionOrder('asc', true)->load();
            $options = $options->toArray();
            if ($options['totalRecords']) {
                foreach ($options['items'] as $option) {
                    if (!empty($option['store_default_value']))
                        $result[] = [$option['option_id'], $option['store_default_value']];
                    else
                        $result[] = [$option['option_id'], $option['value']];
                }
            }
        }

        return $result;
    }

    /**
     * @param EntityAttribute $attribute
     * @param mixed $value
     * @return void
     */
    protected function addSearchCriteria($attribute, $value)
    {
        if (!empty($value)) {
            $this->_searchCriteria[] = ['name' => $attribute->getStoreLabel(), 'value' => $value];
        }
    }

    /**
     *
     * Get the category data
     *
     */
    public function getCategoryNames()
    {
        $result = [];
        $result[] = ['CategoryID', 'Name', 'PageLink', 'ImageLink', 'ParentID'];
        $clientAlias = $this->_helper->ClientAlias;
        $storeIds = $this->_helper->map($clientAlias);

        if (empty($storeIds))
            return $result;

        $collection = $this->_helper->getStoresCategories($storeIds);

        //ResultType - Count
        //just a count of how many rows of data would be provided for the given set of query parameters
        if ($this->_helper->ResultType == 'Count') {
            $customerCount = new stdClass();
            $customerCount->rows = $collection->count();
            return $customerCount;
        }

        $searchResultDataHead = array_map('strtolower', $result[0]);
        $extraFields = $this->_helper->getExtraFields();
        if (!is_null($extraFields)) {
            foreach ($extraFields as $key => $extraField) {
                if (!in_array(strtolower($extraField), $searchResultDataHead))
                    $result[0][] = $extraField;
                else
                    unset($extraFields[$key]);
            }
        }

        $storesDetail = $this->_helper->getStores();
        $storesCode = [];
        foreach($storesDetail as $storeDetail){
            if (in_array($storeDetail['store_id'],$storeIds))
                $storesCode[] = $storeDetail['code'];
        }

        foreach ($collection as $category) {
            $imageUrl = $category->getImageUrl();
            if (!$imageUrl)
                $imageUrl = '';
            $imageUrl = $this->_helper->fixLink($imageUrl);

            $extraFieldsValue = [];
            foreach ($extraFields as $extraField) {
                $extraField = strtolower($extraField);
                $found = false;
                $extraFieldExploded = explode('.', $extraField);
                switch ($extraField) {
                    case (in_array(array_shift($extraFieldExploded), $storesCode)):
                        $viewScopeParam = $extraFieldExploded;
                        foreach ($storesDetail as $storeDetail) {
                            if ($storeDetail['code'] == $viewScopeParam[0]) {
                                $found = true;
                                if ($viewScopeParam[1] == 'url_key_4tell') {
                                    $value = $this->_helper->getCategoryUrlInStore($category, $storeDetail['store_id']);
                                    $value = $this->_helper->fixLink($value);
                                }
                                else
                                    $value = $this->_resourceCategory->getAttributeRawValue($category->getId(), $viewScopeParam[1], $storeDetail['store_id']);
                                $extraFieldsValue[] = ($value) ? $value : "";

                                break;
                            }
                        }
                        break;

                }
                if (!$found)
                    $extraFieldsValue[] = "";
            }

            $categoryUrl = $this->_helper->fixLink($category->getUrl());

            $fieldsValue = [$category->getId(), $category->getName(), $categoryUrl, $imageUrl, $category->getParentId()];
            if (!empty($extraFieldsValue))
                $result[] = array_merge($fieldsValue, $extraFieldsValue);
            else
                $result[] = $fieldsValue;
        }
        return $result;
    }

    /**
     *
     * Get the sales data
     *
     */
    function getSales()
    {
        $result = [];
        //Head
        $result[] = ['OrderID', 'SKU', 'CustomerID', 'Quantity', 'ItemPrice', 'FullPrice', 'Date', 'ModifiedDate'];
        $clientAlias = $this->_helper->ClientAlias;
        $storeIds = $this->_helper->map($clientAlias);
        if (empty($storeIds))
            return $result;

        $collection = $this->_orderItemCollectionFactory->create();

        $collection->addFieldToFilter('main_table.store_id', ['in' => $storeIds]);

        /* TODO: ??? 'product_type' -> 'type_id' */
        //It should send the product ID for the purchased product, and not the Configurable parent ID.
        $collection->addFieldToFilter('product_type', ['neq' => 'configurable']);

        //DateRange
        $dateRange = $this->_helper->getDateRange();

        if ($dateRange) {
            $filterDateRange = [
                'from' => $dateRange[0],
                'date' => true
            ];
            if (isset($dateRange[1])) {
                $plusOneDay = $this->_helper->plusOneDay($dateRange[1], $format = 'Y-m-d');
                $filterDateRange['to'] = $plusOneDay;
            }
            $collection->getSelect()
                ->where("(main_table.created_at >= '" . $filterDateRange['from'] . "' && main_table.created_at <= '" . $filterDateRange['to'] . "') "
                    . " || (main_table.updated_at >= '" . $filterDateRange['from'] . "' && main_table.updated_at <= '" . $filterDateRange['to'] . "') ");
        }

//        if ($this->_helper->DataGroup == 'Returns') {
//            $sales_order_table = $this->_helper->getTableName('sales_order');
//            $collection->getSelect()->joinLeft(array('so' => $sales_order_table), 'main_table.order_id = so.entity_id', array('so.status'));
//            $collection->getSelect()->where("qty_refunded > 0 || status='canceled'");
//        }
        $orderItemBundle = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $orderItemGrouped = [];
        // Loop through the collection data
        $groupedPrice = [];
        foreach ($collection as $item) {
            $productType = $item->getData('product_type');
            if (($productType == 'grouped') && $this->_helper->getConfig(\FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_GROUPPROD, is_null($storeIds) ? $storeIds : $storeIds[0])) {
                if ($productOptions = $item->getData('product_options')) {
                    if (isset($productOptions['super_product_config']['product_id'])) {
                        $groupedQty = $item->getData('qty_ordered') - ($item->getData('qty_canceled') + $item->getData('qty_refunded'));
                        if ($item->getData('status') == 'canceled')
                            $groupedQty =0;

                        if(isset($groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']])) {
                            $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['price'] += $item->getPrice();
                            $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['original_price'] += $item->getData('original_price');
                            $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['groupedQty'] += $groupedQty;
                            if ($groupedQty > 0)
                                $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['count'] += 1;
                        }
                        else {
                            $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['price'] = $item->getPrice();
                            $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['original_price'] = $item->getData('original_price');
                            $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['groupedQty'] = $groupedQty;
                            if ($groupedQty)
                                $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['count'] = 1;
                            else
                                $groupedPrice[$item->getOrderId()][$productOptions['super_product_config']['product_id']]['count']= 0;
                        }
                    }

                }
            }
        }

        // Loop through the collection data
        foreach ($collection as $item) {
            $order = $item->getOrder();
            //Check order existing
            if (!$order->getId()) {
                //Mage::helper('recommend')->log('Order not longer exist.', 'query');
                continue;
            }

            // Get customer ID of use who placed the order
            // if the customer was an account
            $customerId = $order->getData('customer_id');
            if (empty($customerId)) {
                $customerId = $order->getData('customer_email');
            }

            // Get the data for the current item
            $product_type = $item->getData('product_type');
            $zones = $this->_helper->getTimezone($storeIds);
            $zone = $zones[$item->getData('store_id')];
            $dt = new \DateTime($item->getData('created_at'), new \DateTimeZone($zone));
            $createdAt = $dt->format('Y-m-d H:i:sP');
            $dtUpdated = new \DateTime($item->getData('updated_at'), new \DateTimeZone($zone));
            $updatedAt = $dt->format('Y-m-d H:i:sP');

            $price = $item->getPrice();
            $originPrice = $item->getData('original_price');
            $discountedPercent = $item->getDiscountPercent();
            if ($price == 0) {
                if ($item->getData('product_type') == 'simple'){
                    if ($parentItemId = $item->getData('parent_item_id')){
                        $parentItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($parentItemId);
                        $price = $parentItem->getPrice();
                        $originPrice = $parentItem->getData('original_price');
                        $discountedPercent = $parentItem->getDiscountPercent();
                    }
                }

            }

            if($discountedPercent > 0){
                $price = $price - ($price*($discountedPercent/100));
            }            

            // Need to fix method
            $row = $this->_helper->productTypeRules('sales', $item, $storeIds);
            $qty = $row['qty'];
            if ($row) {
                if ($product_type == 'grouped') {
                    if (isset($orderItemGrouped[$item->getOrderId()]) && in_array($row['product_id'], $orderItemGrouped[$item->getOrderId()])) {
                        continue;
                    } else {
                        if(isset($groupedPrice[$item->getOrderId()][$row['product_id']])) {
                            $price = $groupedPrice[$item->getOrderId()][$row['product_id']]['price'];
                            $originPrice = $groupedPrice[$item->getOrderId()][$row['product_id']]['original_price'];
                        }

                        $orderItemGrouped[$item->getOrderId()][] = $row['product_id'];
                        if ($this->_helper->getConfig(\FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_GROUPPROD, is_null($storeIds) ? $storeIds : $storeIds[0]))
                            if($groupedPrice[$item->getOrderId()][$row['product_id']]['count'])
                                $qty = round($groupedPrice[$item->getOrderId()][$row['product_id']]['groupedQty']/$groupedPrice[$item->getOrderId()][$row['product_id']]['count']);
                            else
                                $qty = 0;
                    }
                }
                $price = str_replace(",", "", FourTellHelper::number_format_default_to_zero($price, 2));
                $originPrice = str_replace(",", "", FourTellHelper::number_format_default_to_zero($originPrice, 2));
                $result[] = [$order->getData('increment_id'), $row['sku'], $customerId, (string)$qty, $price, $originPrice, $createdAt, $updatedAt];
                //$row['qty'], $row['qty_canceled'], $price, $dt->format('Y-m-d H:i:sP'));
            }
        }

        //ResultType - Count
        //just a count of how many rows of data would be provided for the given set of query parameters
        if ($this->_helper->ResultType == 'Count') {
            $salesCount = new stdClass();
            $salesCount->rows = count($result) - 1;
            return $salesCount;
        }

        return $result;

    }

    public function getReturns()
    {
        return $this->getSales();
    }

    public function reCreateImageCache($startId)
    {
        $this->searchCriteriaBuilder
            //->setCurrentPage(1)
            ->setPageSize(100);

        $this->searchCriteriaBuilder
            ->addFilters(
                [
                    $this->filterBuilder->setField('entity_id')->setConditionType('gt')
                        ->setValue($startId)->create(),
                ]
            );

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria);
        if ($products->getTotalCount()) {
            foreach ($products->getItems() as $product) {
                $this->_helper->createImageCache($product);
                $startId = $product->getId();
            }
            return $startId;
        }
        return false;
    }

    /**
     * TODO add new @param StoreID and check result
     * Retrieve product stock qty
     *
     * @param Product $product
     * @return float
     */
    public function getProductStockQty($product)
    {
        return $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }

    /**
     * Get full customer name.
     *
     * @param $customerData
     * @return string
     */
    public function getCustomerName($customerData)
    {
        $name = '';
        $name .= $customerData->getFirstname();

        $middleNameMetadata = $this->_customerMetadataService->getAttributeMetadata('middlename');
        if ($middleNameMetadata->isVisible() && $customerData->getMiddlename()) {
            $name .= ' ' . $customerData->getMiddlename();
        }

        $name .= ' ' . $customerData->getLastname();
        return $name;
    }
}
