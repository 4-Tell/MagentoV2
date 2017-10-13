<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Observer;

use Magento\Framework\Event\ObserverInterface;

class RecreateImageCacheObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
	
	/**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @param \Magento\Framework\Message\ManagerInterface
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\Session $backendSession
    ){
        $this->messageManager = $messageManager;
        $this->backendSession = $backendSession;
    }

    /**
     * Add Notice
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->backendSession->setFourTellImageCache(1);
        $this->messageManager->addNoticeMessage( __('The Magento cache was cleared, which also removed the 4-Tell recommendation images. The process to re-create the 4-Tell images has been initiated. 4-Tell images will not be displayed in recommendations until the image re-creation process has completed.'));
    }
}
