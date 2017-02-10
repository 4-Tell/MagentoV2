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
        $orderItemConfigurable = [];
        foreach ($items as $item) {
            if ($item->getData('product_type') == 'configurable') {
                $orderItemConfigurable[$item->getData('item_id')] = str_replace(",", "", number_format($item->getData('price'), 2));
            }
        }
        foreach ($items as $item) {
            $detail = $this->_helper->productTypeRules('tracking',$item);
            if (is_array($detail)) {
                if($item->getData('product_type') == 'simple' && isset($orderItemConfigurable[$item->getData('parent_item_id')])){
                    $price = $orderItemConfigurable[$item->getData('parent_item_id')];
                }
                else {
                    $price = str_replace(",", "", number_format($detail['price'], 2));
                }
                $details[] = array($detail['product_id'], $detail['qty'], $price);
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
        return $this->_checkoutSession->getLastOrderId();
    }

    /**
     * Retrieve current customer id
     *
     * @return int|null
     */
    public function getCurrentCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
}