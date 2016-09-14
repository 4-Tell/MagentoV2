<?php

namespace FourTell\Recommend\Model\Config\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Retrieve list of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            foreach (\Magento\Catalog\Model\Product\Attribute\Source\Status::getOptionArray() as $index => $value) {
                $this->_options[] = ['value' => $index, 'label' => $value];
            }
        }
        return $this->_options;
    }
}
