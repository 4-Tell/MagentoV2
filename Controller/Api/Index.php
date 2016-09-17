<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Controller\Api;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use FourTell\Recommend\Helper\Api as ApiHelper;
use FourTell\Recommend\Model\Feed;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\SecurityViolationException;


class Index extends Action
{

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /** @var ApiHelper */
    protected $_helper;

    /** @var Feed */
    protected $_feed;

    /**
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param ApiHelper $helper
     * @param Feed $feed
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ApiHelper $helper,
        Feed $feed
    )
    {
        $this->_helper = $helper;
        $this->_feed = $feed;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {

            $this->_helper->authorization();

            // check input params
            $error = $this->_helper->checkValidParameters();


            if (!empty($error)) {
                $response = [
                    'success' => false,
                    'message' => $error
                ];
                $resultJson->setHttpResponseCode(400);
                return $resultJson->setData($response);
            }

            $callMethod = $this->_helper->getFeedMethod();
            $response = $this->_feed->$callMethod();
        } catch (SecurityViolationException $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            $resultJson->setHttpResponseCode(403);
        } catch (AuthorizationException $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            $resultJson->setHttpResponseCode(401);

        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $response = [
                'success' => false,
                'message' => __('Oops!')
            ];
            $resultJson->setHttpResponseCode(500);
        }

        $resultJson->setData($response);
        return $resultJson;
    }

}