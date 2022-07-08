<?php

namespace Magesoft\Otp\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CustomerMobileAttribute implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * CustomerAgeAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }


    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }


    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('customer_entity'),
            'mobile',
            [
                'type'     => Table::TYPE_BIGINT,
                'padding'=>11,
                'size'     =>11,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment'  => 'Mobile',
            ]
        );

        $this->moduleDataSetup->getConnection()
            ->addIndex('customer_entity',
                'customer_entity_mobile_uindex',
                ['mobile'],$this->moduleDataSetup->getConnection()::INDEX_TYPE_UNIQUE
            );
        $this->moduleDataSetup->endSetup();
    }
}
