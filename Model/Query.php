<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Model;

use Magento\Framework\App\ResourceConnection;


class Query //extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface

    //    protected $_objectManager; */

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var Resource
     */
    protected $_resource;


    public function __construct(
        ResourceConnection $resource
    )
    {
        $this->_resource = $resource;
    }

    /**
     * Retrieve write connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection();
        }
        return $this->_connection;
    }

    public function getClientAliases($scope = null)
    {
        $connection = $this->_getConnection();
        $tableName = $this->_resource->getTableName('core_config_data');
        $select = $connection->select()
            ->from(array('t' => $tableName))
            ->where('t.path=?', 'recommend/general_settings/client_id');
        if ($scope !== null)
            $select->where('scope= ?', 'stores');
        $select->group('t.value');

        return $connection->fetchAll($select);
    }

    public function getConfigForClientAliases($clientAlias)
    {
        $connection = $this->_getConnection();

        $tableName = $this->_resource->getTableName('core_config_data');

        $select = $connection->select()
            ->from(array('t' => $tableName))
            ->where('t.path=?', 'recommend/general_settings/client_id')
            ->where('value= ?', $clientAlias);
        $data = $connection->fetchAll($select);
        return $data;
    }

    public function getStores()
    {
        $connection = $this->_getConnection();

        //class Switcher extends \Magento\Backend\Block\Template
        $tableName = $this->_resource->getTableName('core_store');
        $select = $connection->select()
            ->from(array('t' => $tableName), array('store_id', 'code', 'name', 'website_id'))
            ->order('sort_order ' . \Magento\Framework\DB\Select::SQL_ASC);
        //->order('sort_order ' . Varien_Db_Select::SQL_ASC);
        $data = $connection->fetchAll($select);
        return $data;
    }

    /**
     * Retrieve array of related bundle product ids by selection product id(s)
     * Fix for //Magento\Bundle\Model\ResourceModel\Selection
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        $connection = $this->_getConnection();
        $mainTable = $this->_resource->getTableName('catalog_product_bundle_selection');
        $productEntityTable = $this->_resource->getTableName('catalog_product_entity');

        $select = $connection->select()->distinct(
            true
        )->from(
            $mainTable,
            ''
        )->join(
            ['e' => $productEntityTable],
            'e.entity_id = ' .  $mainTable . '.parent_product_id',
            ['e.entity_id as parent_product_id']
        )->where(
            $mainTable.'.product_id IN(?)',
            $childId
        );
        return $connection->fetchCol($select);

    }
}
