<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Model\Config\Source;


class Restrict extends \Magento\Framework\App\Config\Value
{
    const XML_PATH_RESTRICT = '';
    const XML_PATH_VALUE = '';
    const MESSAGE = '';

    /**
     * Recommend helper
     *
     * @var \FourTell\Recommend\Helper\Data
     */
    protected $_helper;

    /**
     * Writer of configuration storage
     *
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $_configWriter;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \FourTell\Recommend\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \FourTell\Recommend\Helper\Api $helper,
        array $data = []
    )
    {
        $this->_helper = $helper;
        $this->_configWriter = $configWriter;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate Manufacturer
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value == 1) {
            $restrict_attribute = $this->getData(self::XML_PATH_VALUE);
            if (empty($restrict_attribute)) {
                throw new \Magento\Framework\Exception\LocalizedException(__(self::MESSAGE));
            }
        }

        return $this;
    }

    /**
     * Delete manufacturer attribute from configuration if "Manufacturer, Include in Catalog" option disabled
     *
     * @return $this
     */
    public function afterSave()
    {
        $currentScope = $this->_helper->getCurrentScope();
        $value = $this->getValue();
        if (!$value) {
            $this->_configWriter->delete(
                self::XML_PATH_RESTRICT,
                $currentScope[0],
                $currentScope[1]
            );
        }

        return parent::afterSave();
    }
}