<?php
declare(strict_types=1);


namespace Magesoft\Otp\Model\ResourceModel\MageSoftOptStatus;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\DataObject;
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'magesoft_otp_status_collection';
    protected $_eventObject = 'post_collection';


    /**
     * Define resource model
     *
     * @param DataObject $dataObject
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magesoft\Otp\Model\MageSoftOptStatus',
            'Magesoft\Otp\Model\ResourceModel\MageSoftOptStatus');
    }


}
