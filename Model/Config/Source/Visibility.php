<?php

namespace FourTell\Recommend\Model\Config\Source;

class Visibility implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Email Identity options
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
//            $this->_options[] = ['value' => 0, 'label' => __('None')];
            foreach (\Magento\Catalog\Model\Product\Visibility::getOptionArray() as $index => $value) {
                $this->_options[] = ['value' => $index, 'label' => $value];
            }
        }
        return $this->_options;
    }
}
