<?php
 
namespace FourTell\Recommend\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use FourTell\Recommend\Helper\Data as FourTellHelper;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
 
class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;
 
    public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig       = $eavConfig;
    }
 
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		if (version_compare($context->getVersion(), '2.2019.2.2', '<')) {
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$eavSetup->addAttribute(
				Customer::ENTITY,
				FourTellHelper::FOURTELL_DO_NOT_TRACK_CUSTOMER,
				[
					'type'         => 'int',
					'label'        => 'Do Not Track in 4-Tell',
					'input'        => 'select',
					'required'     => false,
					'visible'      => true,
					'system'       => 0,
					'user_defined' => true,
					'default' 	   => 0,
					'position'     => 85,
					'global'       => ScopedAttributeInterface::SCOPE_WEBSITE,
					'source'       => Boolean::class
				]
			);

			$attribute = $this->eavConfig->getAttribute(Customer::ENTITY, FourTellHelper::FOURTELL_DO_NOT_TRACK_CUSTOMER);
			$attribute->setData('used_in_forms', ['adminhtml_customer']);
			$attribute->save();
		}
    }
}
