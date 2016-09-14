<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Model;

use FourTell\Recommend\Api\FeedInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use \DateTime;
use \stdClass;

/**
 * class Feed
 */
class Feed implements FeedInterface
{
    const MODULE_NAME = 'FourTell_Recommend';
    const XML_CURL_TIMEOUT = 'recommend/general_settings/curl_timeout';

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
    }

    public function getCustomers()
    {
        $this->_result = [];
        $this->resultDataHead = array('CustomerID', 'Name', 'Email', 'PostalCode', 'State');
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
        $clientAlias = $this->_helper->ClientAlias;
        $storeIds = $this->_helper->map($clientAlias);

        //DateRange
        $dateRange = $this->_helper->getDateRange();
        if ($dateRange) {
            $filterDateRange = array(
                'from' => $dateRange[0],
            );

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
        $customerCollection->addFieldToFilter('store_id', array('in' => $storeIds));

        if ($this->_helper->ResultType == 'Count') {
            $customerCount = new stdClass();
            $customerCount->rows = $customerCollection->getSize();
            return $customerCount;
        }

        $items = $customerCollection->getItems();
        foreach ($items as $item) {
            $res = [
                $item->getId(),
                $this->getCustomerName($item),
                $item->getEmail(),
            ];

            if ($item->getDefaultBilling()) {
                $customerAddress = $this->_addressRepository->getById($item->getDefaultBilling());
                $res[] = $customerAddress->getPostcode();
                $res[] = $customerAddress->getRegion()->getRegion();

                if (!empty($extraFields)) {
                    foreach ($extraFields as $extraField) {
                        switch ($extraField) {
                            case 'Address':
                                $street = $customerAddress->getStreet();
                                if (!is_null($street))
                                    $res[] = implode(' ', $street);
                                else
                                    $res[] = '';
                                break;

                            case 'City':
                                $res[] = $customerAddress->getCity();
                                break;

                            case 'Phone':
                                $res[] = $customerAddress->getTelephone();
                                break;

                            case 'Country':
                                $res[] = $customerAddress->getCountryId();
                                break;
                            default:
                                $res[] = '';
                        }
                    }
                }
            } else {
                //'PostalCode', 'State'
                $res[] = '';
                $res[] = '';
                foreach ($extraFields as $extraField) {
                    $res[] = '';
                }
            }
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
                $this->resultDataHead = array('ProductID', 'Inventory');

                break;

            default:
                $this->resultDataHead = array('ProductID', 'Name', 'CategoryIDs', 'ManufacturerID', 'Price', 'SalePrice', 'ListPrice', 'Cost', 'Inventory', 'Visible', 'Link', 'ImageLink', 'Ratings', 'StandardCode', 'ParentID', 'ProductType', 'Visibility', 'StockAvailability');

                break;
        }
        // Create file header
        $this->resultData = [];
        $searchResultDataHead = array_map('strtolower', $this->resultDataHead);
        $extraFields = $this->_helper->getExtraFields();
        if (!is_null($extraFields)) {
            foreach ($extraFields as $key => $extraField) {
                $extraField = strtolower($extraField);
                if (!in_array($extraField, $searchResultDataHead))
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
        $trueProductIds = array();
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
        $collection->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'type_id'
        )->addAttributeToSelect('*');
        //DateRange
        $dateRange = $this->_helper->getDateRange();

        if ($dateRange) {
            $filterDateRange = array(
                'from' => $dateRange[0],
                'date' => true
            );
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
        $bundleProductModel = $objectManager->get('Magento\Bundle\Model\Product\Type');
        $stockFlag = 'has_stock_status_filter';
        $collection->setFlag($stockFlag, true);


        if ($this->_helper->getFeedMethod() == 'getInventory') {
            foreach ($collection as $product) {
                $productId = $product->getEntityId();
                $manageStock = $this->stockRegistry->getStockItem($product->getId())->getUseConfigManageStock();
                if ($manageStock)
                    $qty = number_format($this->getProductStockQty($product), 0);
                else
                    $qty = '';

                //Set the inventory to zero for those products that are "Disabled
                $status = $product->getStatus();
                if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)
                    $qty = 0;
                //Set the inventory to zero for those products that are labeled "Out Of Stock"
                if ($this->stockRegistry->getProductStockStatus($product->getId())) {
                    $stockAvailability = 'In Stock';
                } else {
                    $stockAvailability = 'Out of Stock';
                    $qty = 0;
                }
                $this->resultData[] = array($productId, $qty);
            }
            return $this->resultData;
        }
        foreach ($collection as $product) {
            $parentIds = [];
            $productId = $product->getEntityId();
            $cat = implode(",", $product->getCategoryIds());
            $manufacturerValue = '';
            if (!empty($manufacturerCode)) {
//                try {
                $manufacturerValue = $product->getData($manufacturerCode);
                if (is_null($manufacturerValue))
                    $manufacturerValue = '';
//                } catch (Exception $e) {
//                    $manufacturerValue = '';
//                }
            }

            $qty = $this->getProductStockQty($product);
            $productUrl = $product->getUrlModel()->getUrl($product);
            if (is_null($productUrl))
                $productUrl = '';
            $image = $this->_helper->getImageUrl($product, $storeIds[0]);
            $visibility = $product->getVisibility();
            $visibilityOptions = \Magento\Catalog\Model\Product\Visibility::getOptionArray();
            $visible = 1;
            $status = $product->getStatus();
            if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)
                $qty = 0;

            if ($this->stockRegistry->getProductStockStatus($product->getId())) {
                $stockAvailability = 'In Stock';
            } else {
                $stockAvailability = 'Out of Stock';
                $qty = 0;
            }

            $priceList = $product->getData('msrp');
            $priceCost = $product->getData('cost');
            if ($specialPrice = $product->getSpecialPrice()) {
                $specialPrice = str_replace(",", "", number_format($specialPrice, 2));
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

            if ($visibility == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE ||
                $status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
            )
                $visible = 0;

            if ($product->getTypeId() == "simple") {
                //No Grouped/Bundled Parent IDs in Catalog Feed for Simple Products if simple not visible individually
                if ($product->getVisibility() == 1) {
                    $parentIdArray = $configurableProductModel->getParentIdsByChild($productId);
                    if (isset($parentIdArray[0])) {
                        $parentIds = array_merge($parentIds, $parentIdArray);
                    }
                    $parentIdArray = $groupedProductModel->getParentIdsByChild($productId);
                    if (isset($parentIdArray[0])) {
                        $parentIds = array_merge($parentIds, $parentIdArray);
                    }
                }
                $parentIdArray = $bundleProductModel->getParentIdsByChild($productId);
                if (isset($parentIdArray[0])) {
                    $parentIds = array_merge($parentIds, $parentIdArray);
                }
            }
            if (isset($parentIds[0])) {
                $parentIds = implode(',', $parentIds);
            } else
                $parentIds = "";

            if (!$summaryData = $product->getRatingSummary()) {
                $this->_reviewFactory->create()->getEntitySummary($product, $storeIds[0]);
            }
            $summaryData = $product->getRatingSummary();
            $ratingPercent = $summaryData->getRatingSummary();
            //5 star
            if ($ratingPercent)
                $avg = round($ratingPercent * 5 / 100);
            else
                $avg = '';

            $resultData = array(
                $productId,
                $product->getName(),
                $cat,
                $manufacturerValue,
                number_format($product->getPrice(), 2, '.', ''),
                $specialPrice,
                number_format($priceList, 2, '.', ''),
                number_format($priceCost, 2, '.', ''),
                number_format($qty, 0),
                $visible,
                $productUrl,
                $image,
                $avg,
                $product->getSku(),
                $parentIds,
                $product->getTypeId(),
                $visibilityOptions[$visibility],
                $stockAvailability
            );

            $this->resultData[] = $resultData;
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
            'curlTimeout' => $this->_helper->getConfig(self::XML_CURL_TIMEOUT),
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
        $result[] = array('ID', 'Name');
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
                        $result[] = array($option['option_id'], $option['store_default_value']);
                    else
                        $result[] = array($option['option_id'], $option['value']);
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
        $result[] = ['CategoryID', 'Name'];

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

        foreach ($collection as $category) {
            $result[] = [$category->getId(), $category->getName()];
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
        $result[] = array('OrderID', 'ProductID', 'CustomerID', 'Quantity', 'Date');
        $clientAlias = $this->_helper->ClientAlias;
        $storeIds = $this->_helper->map($clientAlias);
        if (empty($storeIds))
            return $result;

        $collection = $this->_orderItemCollectionFactory->create();

        $collection->addFieldToFilter('main_table.store_id', ['in' => $storeIds]);

        /* TODO: ??? 'product_type' -> 'type_id' */
        //It should send the product ID for the purchased product, and not the Configurable parent ID.
        $collection->addFieldToFilter('product_type', array('neq' => 'configurable'));

        //DateRange
        $dateRange = $this->_helper->getDateRange();

        if ($dateRange) {
            $filterDateRange = array(
                'from' => $dateRange[0],
                'date' => true
            );
            if (isset($dateRange[1])) {
                $plusOneDay = $this->_helper->plusOneDay($dateRange[1], $format = 'Y-m-d');
                $filterDateRange['to'] = $plusOneDay;
            }
            $collection->addFieldToFilter('created_at', $filterDateRange);
        }

        if ($this->_helper->DataGroup == 'Returns') {
            $sales_order_table = $this->_helper->getTableName('sales_order');
            $collection->getSelect()->joinLeft(array('so' => $sales_order_table), 'main_table.order_id = so.entity_id', array('so.status'));
            $collection->getSelect()->where("qty_refunded > 0 || status='canceled'");
        }


        $order_item_bundle = array();
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
            if ($customerId == '') {
                // Get email of use who placed the order if
                // the order was placed with guest checkout
                $customerId = $order->getData('customer_email');
                if ($customerId == '') {
                    // Get order ID as last resort to use
                    // as the customer ID
                    $customerId = $order->getData('increment_id');
                } else {
                    // If we ended up with an email address then
                    // make into a hash so we are not transmitting
                    // or storing readable personal information
                    $customerId = md5($customerId);
                }
            }

            // Get the data for the current item
            $order_id = $order->getData('increment_id');
            //$product_id = $item->getData('product_id');
            $product_type = $item->getData('product_type');

            // TODO: instead $row['qty'] ???
//            if ($this->_helper->DataGroup == 'Returns'){
//                $qty_refunded = $item->getData('qty_refunded');
//                $qty_canceled = $item->getData('qty_canceled');
//                $qty_ordered = ($qty_refunded + $qty_canceled) * (-1);
//            }else{
//                $qty_ordered = $item->getData('qty_ordered');
//            }

            $created_at = $item->getData('created_at');
            $dt = new DateTime($created_at);

            // Need to fix method
            $row = $this->_helper->productTypeRules('sales', $item, $storeIds);

            if ($row) {
                if ($product_type == 'grouped') {
                    if (isset($order_item_bundle[$order_id]) && in_array($row['product_id'], $order_item_bundle[$order_id])) {
                        continue;
                    } else {
                        $order_item_bundle[$order_id][] = $row['product_id'];
                    }
                }
                $result[] = array($order->getData('increment_id'), $row['product_id'], $customerId, $row['qty'], $dt->format('Y-m-d'));
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