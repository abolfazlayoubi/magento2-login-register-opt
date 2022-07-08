<?php


namespace Magesoft\Otp\Model\Queue;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magesoft\Otp\Model\Data\MessageQueue;

class QueueManagement
{

    const SMS_SENDER_TOPIC="magesoft.sms";
    /**
     * @var PublisherInterface
     */
    protected $publisherInterface;


    /**
     * QueueManagement constructor.
     * @param PublisherInterface $publisherInterface
     */
    public function __construct(
        PublisherInterface $publisherInterface
    )
    {
        $this->publisherInterface=$publisherInterface;
    }


    /**
     * @param string $topic
     * @param array $data
     */
    public function publishToQueue(string $topic, array $data){
        try{
            switch ($topic){
                case self::SMS_SENDER_TOPIC:
                    $obj=new MessageQueue;
                    $obj->setId($data['id']);
                    $obj->setAuthCode($data['authCode']);
                    $obj->setMobile($data['mobile']);
                    $this->publisherInterface->publish($topic,$obj);
            }
        }catch (\Exception $e){
            throw new \Exception("publish to topic name is:".$topic." ".$e->getMessage());
        }


    }
}
