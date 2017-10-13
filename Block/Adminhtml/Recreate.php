<?php
namespace FourTell\Recommend\Block\Adminhtml;
use Magento\Backend\Block\Template;
class Recreate extends Template
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        Template\Context $context,
        \Magento\Backend\Model\Session $backendSession,
        array $data = []
    ) {
        parent::__construct($context, $data);  
        $this->backendSession = $backendSession;
    }

    /**
     * Return IsRequiredRecreateCache
     *
     * @return boolean
     */
    public function getIsRequiredRecreateCache()
    {
        if ($this->backendSession->getFourTellImageCache()){
            $this->backendSession->unsFourTellImageCache();
            return true;
        }
        return false;
    }

    /**
     * Return ajax url for recreate cache
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('recommendadmin/config');
    }
}