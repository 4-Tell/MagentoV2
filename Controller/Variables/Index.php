<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Controller\Variables;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;


class Index extends Action
{
    /**
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

        return $resultLayout;
    }
}
