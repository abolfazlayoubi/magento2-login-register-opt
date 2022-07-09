<?php
declare(strict_types=1);

namespace Magesoft\Otp\Model;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magesoft\Otp\Model\MageSoftOptStatus;
use Magesoft\Otp\Model\RedisStorage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface as DateTime;
use Magesoft\Otp\Model\Queue\QueueManagement;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
class OtpManager
{

    public const OTP_PENDING_STATUS=1;
    public const OTP_Failed_STATUS=2;
    public const OTP_SEND_STATUS=3;

    public const OTP_ATTRIBUTES=[
        'mobile'=>[
            'key'=>'mobile',
            'type'=>'static',
            'input'=>'text',
            'unique'=>true,
            'system'=>false,
            'validate'=>[
                'input_validation'=>'number',
                'max_text_length'=>10,
                'min_text_length'=>10,
            ]
        ]
    ];

    public const MOBILE_KEY=self::OTP_ATTRIBUTES['mobile']['key'];
    /**
     * @var CustomerCollection
     */
    protected CustomerCollection $customerCollection;

    /**
     * @var MageSoftOptStatus
     */
    protected MageSoftOptStatus $mageSoftOptStatus;

    /**
     * @var RedisStorage
     */
    protected RedisStorage $redisStorage;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var QueueManagement
     */
    protected QueueManagement $queueManagement;


    /**
     * @var CustomerInterface
     */
    protected CustomerInterface $customerInterface;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepInterface;

    /**
     * @param CustomerCollection $customerCollection
     * @param MageSoftOptStatus $mageSoftOptStatus
     * @param RedisStorage $redisStorage
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param QueueManagement $queueManagement
     * @param CustomerInterface $customerInterface
     * @param CustomerRepositoryInterface $customerRepInterface
     */
    public function __construct(
        CustomerCollection $customerCollection,
        MageSoftOptStatus $mageSoftOptStatus,
        RedisStorage $redisStorage,
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        QueueManagement $queueManagement,
        CustomerInterface $customerInterface,
        CustomerRepositoryInterface $customerRepInterface
    )
    {
        $this->customerCollection=$customerCollection;
        $this->mageSoftOptStatus=$mageSoftOptStatus;
        $this->redisStorage=$redisStorage;
        $this->scopeConfig=$scopeConfig;
        $this->dateTime=$dateTime;
        $this->queueManagement=$queueManagement;
        $this->customerInterface=$customerInterface;
        $this->customerRepInterface=$customerRepInterface;
    }


    /**
     * @param int $visitorId
     * @param int $mobile
     * @return int
     * @throws \Exception
     */
    public function generateCodeAndSend(int $visitorId, int $mobile):int{
        $code=$this->generateCode();
        $customer=$this->customerCollection
            ->addFieldToFilter(self::MOBILE_KEY,$mobile);
        $expireTime = new \DateTime($this->dateTime
            ->date()->format('Y-m-d H:i:s'));
        $expireTime->modify('+'.$this->getAuthExpireTime().' minute');
        $this->redisStorage
            ->setData("visitorId_".$visitorId,
                $this->redisStorage
                    ->getDataStorageClass()
                    ->setLifeTime($this->getAuthExpireTime())
                    ->setName('auth')
                    ->setType('int')
                    ->setValue([
                        'authCode'=>$code,
                        'expire'=>$expireTime->getTimestamp()
                    ])
            );
        $id=intval($this->addData(
            $visitorId,
            ($customer->getSize()==0)?null:intval($customer->getFirstItem()->getId()),
            $code,
            $mobile
        )->getId());
        $this->queueManagement->publishToQueue($this->queueManagement::SMS_SENDER_TOPIC,
            [
                'id'=>$id,
                'mobile'=>$mobile,
                'authCode'=>$code,
            ]
            );
        return $id;

    }

    /**
     * @return int
     */
    private function generateCode(): int
    {
        return rand(11111,99999);
    }

    /**
     * @param int $visitorId
     * @param int $mobile
     * @return bool
     */
    public function checkRequestBefore(int $visitorId,int $mobile): bool
    {
        return boolval(count($this->redisStorage->redisKeyWalk("visitorId_".$visitorId)));
    }

    /**
     * @return int
     */
    public function getAuthExpireTime(): int{
        return intval($this->scopeConfig->getValue('otp_base_settings/auth_setting/expire_auth'))*60;
    }


    /**
     * @param int $visitorId
     * @param $customerId
     * @param int $code
     * @param int $mobileNumber
     * @return \Magesoft\Otp\Model\MageSoftOptStatus
     * @throws \Exception
     */
    private function addData(
        int $visitorId,
        $customerId,
        int $code,
        int $mobileNumber
    ):\Magesoft\Otp\Model\MageSoftOptStatus{
        try{
            return $this->mageSoftOptStatus
                ->addData([
                    'visitor_id'=>$visitorId,
                    'customer_id'=>$customerId,
                    'code'=>$code,
                    'mobile_number'=>$mobileNumber
                ])->save();
//            $obj=$this->mageSoftOptStatus
//                ->getDataObject()
//                ->addData();
//            $this->mageSoftOptStatus
//                ->getCollection()
//                ->addItem($obj);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * @param int $id
     * @param int $status
     * @return mixed
     * @throws \Exception
     */
    public function changeStatus(int $id, int $status){
        try{
            $item=$this->mageSoftOptStatus
                ->getCollection()
                ->addFieldToFilter('entity_id',$id);
            if ($item->getSize()){
                return $item->getFirstItem()
                    ->setDeliveryStatus($status)
                    ->save();
            }else{
                throw new \Exception("item with This id does not exist");
            }

        }catch (\Exception $e){
            throw new \Exception("Change Status:".$e->getMessage());

        }
    }


    /**
     * @param int $mobile
     * @param int $code
     * @return bool
     * @throws \Exception
     */
    public function sendSms(int $id,int $mobile,int $code):bool {
        try{
            $this->changeStatus($id,self::OTP_SEND_STATUS);
            return true;
        }catch (\Exception $e){
            $this->changeStatus($id,self::OTP_Failed_STATUS);
            throw new \Exception("Send SMS:".$e->getMessage());
        }
    }


    /**
     * @return ResourceModel\MageSoftOptStatus\Collection
     */
    public function getFailedRows():ResourceModel\MageSoftOptStatus\Collection{
        return $this->mageSoftOptStatus
            ->getCollection()
            ->addFieldToFilter('delivery_status',self::OTP_Failed_STATUS);
    }


    /**
     * @param int $id
     * @param int $visitorId
     * @return int
     * @throws \Exception
     */
    public function getCustomerIdByItemId(int $id, int $visitorId):int{
       try{
           $item=$this->mageSoftOptStatus
               ->getCollection()
               ->addFieldToFilter('entity_id',$id)
               ->addFieldToFilter('delivery_status',self::OTP_PENDING_STATUS)
               ->addFieldToFilter('visitor_id',$visitorId);
           if ($item->getSize()){
               $customer=clone $this->customerCollection;
               $customer->addFieldToFilter('mobile',$item->getFirstItem()->getMobileNumber());
               if($customer->getSize()){
                   return intval($customer->getFirstItem()->getId());
               }else{
                    $obj=clone $this->customerInterface;
                    $obj
                        ->setEmail($item->getFirstItem()->getMobileNumber()."@otp.com")
                        ->setFirstname("1")
                        ->setLastname("2")
                        ->setCustomAttribute("mobile",$item->getFirstItem()->getMobileNumber())
                    ;
                    $newCustomer=$this->customerRepInterface
                        ->save($obj);

                    return intval($newCustomer->getId());
               }
           }else{
               throw new \Exception("Request is not valid");
           }
       }catch (\Exception $e){
           throw new \Exception("Login By Id:".$e->getMessage());
       }
    }
}
