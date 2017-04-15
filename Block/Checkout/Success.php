<?php
/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */
namespace FourTell\Recommend\Block\Checkout;

class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Recommend helper
     *
     * @var \FourTell\Recommend\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \FourTell\Recommend\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \FourTell\Recommend\Helper\Data $helper,
        array $data = []
    ) {
        $this->_order = $order;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        parent::__construct($context, $data);
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
     * Retrieve current order details
     *
     * @return json
     */
    public function getOrderDetails()
    {
        $incrementId = $this->_checkoutSession->getLastRealOrderId();
        $order = $this->_order->loadByIncrementId($incrementId);
        $items = $order->getAllItems();
        $details = [];
        $orderItemGrouped = [];
        $orderItemConfigurable = [];
        $orderItemSkip = [];
        $groupedPrice = [];

        $configGroupprod = $this->_scopeConfig->getValue(
            \FourTell\Recommend\Helper\Data::XML_PATH_ADVANCED_GROUPPROD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        foreach ($items as $item) {
            if ($item->getData('product_type') == 'configurable') {
                $orderItemConfigurable[$item->getData('item_id')] = str_replace(",", "", number_format($item->getData('price'), 2));
            }
            if ($item->getData('product_type') == 'grouped') {
                if ($configGroupprod) {
                    if ($productOptions = $item->getData('product_options')) {
                        $groupedQty = $item->getData('qty_ordered') - ($item->getData('qty_canceled') + $item->getData('qty_refunded'));
                        if ($item->getData('status') == 'canceled')
                            $groupedQty =0;
                        if (isset($productOptions['info_buyRequest']['super_product_config']['product_id'])) {
                            $productGroupedId = $productOptions['info_buyRequest']['super_product_config']['product_id'];
                            if (isset($orderItemGrouped[$productGroupedId])) {
                                $orderItemGrouped[$productGroupedId] += $item->getPrice();
                                $groupedPrice[$productGroupedId]['groupedQty'] += $groupedQty;
                                if ($groupedQty > 0)
                                    $groupedPrice[$productGroupedId]['count'] += 1;
                            }
                            else {
                                $orderItemGrouped[$productGroupedId] = $item->getPrice();
                                $groupedPrice[$productGroupedId]['groupedQty'] = $groupedQty;
                                if ($groupedQty)
                                    $groupedPrice[$productGroupedId]['count'] = 1;
                                else
                                    $groupedPrice[$productGroupedId]['count']= 0;
                            }
                        }
                    }
                }
            }

        }

        foreach ($items as $item) {
            $productType = $item->getData('product_type');
            $detail = $this->_helper->productTypeRules('tracking',$item);
            if ($detail) {
                if ($productType == 'grouped') {
                    if (isset($orderItemSkip) && in_array($detail['product_id'], $orderItemSkip)) {
                        continue;
                    } else {
                        $orderItemSkip[] = $detail['product_id'];
                    }
                }
                if($productType == 'simple' && isset($orderItemConfigurable[$item->getData('parent_item_id')])){
                    $price = $orderItemConfigurable[$item->getData('parent_item_id')];
                }
                elseif (isset($orderItemGrouped[$detail['product_id']])){
                    $price = str_replace(",", "", number_format($orderItemGrouped[$detail['product_id']], 2));
                    if($groupedPrice[$detail['product_id']]['count'])
                        $detail['qty'] = round($groupedPrice[$detail['product_id']]['groupedQty']/$groupedPrice[$detail['product_id']]['count']);
                    else
                        $detail['qty'] = 0;
                }
                else {
                    $price = str_replace(",", "", number_format($item->getData('price'), 2));
                }
                $details[] = array($detail['sku'], $detail['qty'], $price);
            }
        }
        return \Zend_Json::encode($details);
    }

    /**
     * Retrieve current order id
     *
     * @return int|null
     */
    public function getCurrentOrderId()
    {
        return $this->_checkoutSession->getLastRealOrderId();
    }

    /**
     * Retrieve current customer id
     *
     * @return int|null
     */
    public function getCurrentCustomerId()
    {
        $customerId = $this->_customerSession->getCustomerId();
        if (is_null($customerId)){
            $incrementId = $this->_checkoutSession->getLastRealOrderId();
            $order = $this->_order->loadByIncrementId($incrementId);
            $customerId = $order->getData('customer_email');
        }

        return $customerId;
    }
}