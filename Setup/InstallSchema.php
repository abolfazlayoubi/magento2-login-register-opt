<?php
namespace Magesoft\Otp\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magesoft\Otp\Model\OtpManager;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var OtpManager
     */
    protected $otpManager;

    /**
     * CustomerAgeAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param OtpManager $otpManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        OtpManager $otpManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->otpManager=$otpManager;
    }

    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->createTable(
            $installer->getConnection()->newTable($setup->getTable('magesoft_opt_status'))
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'visitor_id',
                    Table::TYPE_BIGINT,
                    20,
                    ['unsigned' => true, 'nullable' => false],
                    'Visitor ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'default' => null],
                    'Customer ID'
                )
                ->addColumn(
                    'mobile_number',
                    Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false],
                    'Mobile Number has Requested'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_INTEGER,
                    6,
                    ['unsigned' => true, 'nullable' => false],
                    'Auth Code'
                )
                ->addColumn(
                    'delivery_status',
                    Table::TYPE_INTEGER,
                    2,
                    ['unsigned' => true, 'nullable' => false, 'default' => 1],
                    'Auth Code'
                )->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    "",
                    ['nullable' => false , 'default' => Table::TIMESTAMP_INIT],
                    "message"
                )->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    "",
                    ['nullable' => false , 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    "message"
                )->addColumn(
                    'try_times',
                    Table::TYPE_INTEGER,
                    2,
                    ['unsigned' => true, 'nullable' => false, 'default' => 0],
                    'Try Times'
                )->addColumn(
                    'last_time_try',
                    Table::TYPE_TIMESTAMP,
                    "",
                    ['nullable' => true , 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    "Last Time Try"
                )
                ->addForeignKey(
                    $setup
                        ->getFkName('magesoft_opt_status',
                            'visitor_id', 'customer_visitor',
                            'visitor_id'),
                    'visitor_id',
                    $setup->getTable('customer_visitor'),
                    'visitor_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
                )
                ->addForeignKey(
                    $setup
                        ->getFkName('magesoft_opt_status',
                            'customer_id', 'customer_entity',
                            'entity_id'),
                    'customer_id',
                    $setup->getTable('customer_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
        );
        $installer->endSetup();

        /** @var CustomerSetup $eavSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        foreach ($this->otpManager::OTP_ATTRIBUTES as $key=>$att){
            $customerSetup->addAttribute(
                Customer::ENTITY,
                $att['key'],
                [
                    'type' => $att['type'],
                    'label' => $att['key'],
                    'input' => $att['input'],
                    'required' => false,
                    'unique' =>$att['unique'],
                    'sort_order' => 130,
                    'visible' => true,
                    'position'  => 130,
                    'user_defined' => true,
                    'system'  => $att['system'],
                    'validate_rules'=>json_encode($att['validate']),
                    'is_used_in_grid'=>true,
                    'is_visible_in_grid'=>true,
                    'is_filterable_in_grid'=>true,
                    'is_searchable_in_grid'=>true
            ]
            );
            $mobileAttribute = clone $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mobile');

            $mobileAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer', 'customer_account_create', 'customer_account_edit'],
            ]);

            $mobileAttribute->save();
            $customerSetup = clone $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        }




    }
}
