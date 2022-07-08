<?php
declare(strict_types=1);

namespace Magesoft\Otp\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MageSoftOptStatus extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magesoft_opt_status', 'entity_id');
    }


}
