<?php
/**
 * 4-Tell Product Recommendations
 *
 * @package FourTell_Recommend
 * @copyright 4-Tell, Inc.
 */
namespace FourTell\Recommend\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class Recommend implements SectionSourceInterface
{
    /**
     * Recommend helper
     *
     * @var \FourTell\Recommend\Helper\Data
     */
    protected $_helper;

    /**
     * @param \FourTell\Recommend\Helper\Data $helper
     */
    public function __construct(
        \FourTell\Recommend\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return ['data' => $this->getExtraData()];
    }

    /**
     * Returns parameters that should appear in the loader code
     *
     * @return string
     */
    public function getExtraData()
    {
        $res = $this->_helper->getExtraData();
        return $res;
    }
}