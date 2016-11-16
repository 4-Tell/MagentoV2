<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Helper;

use \DateTime;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\SecurityViolationException;


class Api extends Data
{

    const XML_SSL_REQUIRED = 'recommend/general_settings/ssl_required';
    const XML_SERVICE_KEY = 'recommend/general_settings/service_key';

    protected $_validKeys = array('ClientAlias','ServiceKey','DataGroup','ResultType','Mode','DateRange','RowRange','ExtraFields');
    protected $_validValues = array(
        'DataGroup' => array('Catalog','Sales','CategoryNames', 'ManufacturerNames', 'Customers', 'Version', 'Inventory', 'Returns'),
        'ResultType' => array('Data','Count')
    );

    public $ClientAlias=null;
    protected $ServiceKey=null;
    public $DataGroup='Catalog';
    public $ResultType='Data';
    protected $DateRange='All';
    protected $RowRange='All';
    protected $ExtraFields=[];

    public function getExtraFields(){
        $result = $this->ExtraFields;
        if (!is_null($this->ExtraFields) && !is_array($this->ExtraFields)){
            $result = explode(",", $this->ExtraFields);
            $result = array_unique($result);
            $this->ExtraFields = $result;
        }
        return $result;
    }

    public function getDateRange(){
        if ($this->DateRange != 'All')
            return explode(",", $this->DateRange);
        else
            return false;
    }

    public function getRowRange(){
        if ($this->RowRange != 'All'){
            $result = explode(",", $this->RowRange);
            $result[0] = (int)$result[0] - 1;
            $result[1] = (int)$result[1];
            return $result;
        }
        else
            return false;
    }

    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function plusOneDay($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        //PHP 5 >= 5.2.0
        $d->modify('+1 day');
        return $d->format($format);
    }

    public function checkValidParameters(){
        $params = $this->_request->getParams();
        $error = array();

        foreach ($params as $key => $param) {
            if (!in_array($key,$this->_validKeys))
                $error['not_allowed_properties'][] = $key;
            elseif( isset($this->_validValues[$key]) && !in_array($param, $this->_validValues[$key])){
                $error['not_allowed_values'][] = array($key=>$param);
            }
            else
                $this->$key = $param;
        }

        //validate Data Range
        $dateRange = $this->getDateRange();
        $isValidDateRange = true;
        if ($dateRange){
            if (isset($dateRange[0]))
                if(!$this->validateDate($dateRange[0]))
                    $isValidDateRange = false;

            if (isset($dateRange[1]))
                if(!$this->validateDate($dateRange[1]))
                    $isValidDateRange = false;

            if (!$isValidDateRange)
                $error['not_allowed_values'][]['DateRange'] = $this->DateRange;
        }

        //validate RowRange
        $isValidRowRange = true;
        if ($this->RowRange != 'All'){
            $result = explode(",", $this->RowRange);
            if (count($result) != 2)
                $isValidRowRange = false;

            if (isset($result[0])){
                $result[0] = (int) $result[0];
                if ($result[0] <= 0 )
                    $isValidRowRange = false;
            }
            if (isset($result[1])){
                $result[1] = (int) $result[1];
                if ($result[1] <= 0 )
                    $isValidRowRange = false;
            }
        }
        if (!$isValidRowRange)
            $error['not_allowed_values'][]['RowRange'] = $this->RowRange;

        return $error;
    }

    public function getFeedMethod(){
        $feedMethod = 'get'.$this->DataGroup;
        return $feedMethod;
    }

    public function checkSecretParameters(){
        $serviceKey = $this->_request->getParam('ServiceKey');
        $configServiceKey = $this->_getApiServiceKey();
        if(!$serviceKey || $serviceKey != $configServiceKey) {
            return false;
        }

        $clientAlias = $this->_request->getParam('ClientAlias');

        //check clientAlias
        $configClientAliases = $this->_getClientAliases();
        if ($configClientAliases) {
            foreach ($configClientAliases as $configClientAlias) {
                if ($configClientAlias['value'] === $clientAlias) {
                    return true;
                }
            }
        }
        return false;
    }

    private function _getApiServiceKey()
    {
        // Grab existing service key from the admin scope
        // tyt treba testutu -> 0 пробувати передати $store = 0
        $service_key = $this->getConfig(self::XML_SERVICE_KEY);
        if( (!$service_key || strlen(trim($service_key)) == 0)) {
            return false;
        }

        return $service_key;
    }

    public function getSslRequired()
    {
        return $this->getConfig(self::XML_SSL_REQUIRED);
    }


    /**
     * Authorization Checks
     * @return true|Exception
     */
    public function authorization()
    {
        //check if secure mode
        $isSecure = $this->_getRequest()->isSecure();
        $isSslRequired = $this->getSslRequired();
        // check to see if your store is in secure mode
        if (!$isSecure && $isSslRequired) {
            //your page is in HTTP mode
            throw new SecurityViolationException(__('Non-https requests'));
        }
        // check the token
        $authorise = $this->checkSecretParameters();
        if (!$authorise) {
            //throw new \Magento\Framework\Exception\AuthorizationException(__('Not authorised'));
            throw new AuthorizationException(__('Not authorised'));
        }
        return true;
    }
}
