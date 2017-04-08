<?php
/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */
namespace FourTell\Recommend\Block\Html;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
/**
 * Class Loader
 * @package FourTell\Recommend\Block\Html
 */
class Variables extends Template implements IdentityInterface
{
    const CACHE_TAG = 'recommend_variables_block';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get URL for ajax call
     *
     * @return string
     */
    public function getVariablesUrl()
    {
        return $this->getUrl(
            'recommend/variables',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id' => $this->getProductId(),
                'category' => $this->getCategoryId()
            ]
        );
    }

    /**
     * Get current product id
     *
     * @return null|int
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('current_product');
        return $product ? $product->getId() : null;
    }

    /**
     * Get current product id
     *
     * @return null|int
     */
    public function getCategoryId()
    {
        if($this->_request->getFullActionName() == 'catalog_category_view')
            $category = $this->_coreRegistry->registry('current_category');
        return (isset($category)) ? $category->getId() : null;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }
}