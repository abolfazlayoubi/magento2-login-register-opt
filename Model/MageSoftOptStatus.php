<?php
declare(strict_types=1);

namespace Magesoft\Otp\Model;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magesoft\Otp\Model\ResourceModel\MageSoftOptStatus\Collection as MageSoftOptStatusCollection;
use Magento\Framework\DataObject;
class MageSoftOptStatus extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'mage_opt_status';

    protected $_cacheTag = 'mage_opt_status';

    protected $_eventPrefix = 'mage_opt_status';


    /**
     * @var MageSoftOptStatusCollection
     */
    protected $mageSoftOptStatusCollection;

    /**
     * @var DataObject
     */
    protected $dataObject;

    /**
     * MageSoftOptStatus constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param MageSoftOptStatusCollection $mageSoftOptStatusCollection
     * @param DataObject $dataObject
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        MageSoftOptStatusCollection $mageSoftOptStatusCollection,
        DataObject $dataObject
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->mageSoftOptStatusCollection=$mageSoftOptStatusCollection;
        $this->dataObject=$dataObject;
    }

    protected function _construct()
    {
        $this->_init('Magesoft\Otp\Model\ResourceModel\MageSoftOptStatus');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getCollection()
    {
        return $this->mageSoftOptStatusCollection;
    }

    public function getDataObject(){
        return $this->dataObject;
    }
}
