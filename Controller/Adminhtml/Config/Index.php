<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use FourTell\Recommend\Helper\Api as ApiHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use FourTell\Recommend\Model\Feed;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected $_helper;

    protected $_feed;
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param ApiHelper $helper
     * @param Feed $feed
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ApiHelper $helper,
        Feed $feed
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_feed = $feed;
    }

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('FourTell_Recommend::recommend_config');
    }

    public function execute()
    {
        $startId = $this->_request->getParam('startId');
        $resultJson = $this->resultJsonFactory->create();
        $res = $this->_feed->reCreateImageCache($startId);
        if ($res)
            $response = ['status'=>'success', 'startId' => (int)$res];
        else
            $response = ['status'=>'stop'];
        $resultJson->setHttpResponseCode(200);

        return $resultJson->setData($response);
    }
}