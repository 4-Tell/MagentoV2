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
     * @param \Magento\Framework\Message\ManagerInterface
     */
    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Add Notice
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->messageManager->addNoticeMessage( __('Please select ‘Recreate 4-Tell Image Cache’ button in 4-Tell Boost > Display Settings tab so 4-Tell uses the new image cache.'));
    }
}
