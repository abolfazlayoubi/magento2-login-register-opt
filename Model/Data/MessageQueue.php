<?php


namespace Magesoft\Otp\Model\Data;

use Magesoft\Otp\Api\Data\MessageQueue as MessageQueueInterface;
class MessageQueue implements MessageQueueInterface
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $mobile;

    /**
     * @var int
     */
    protected $authCode;

    /**
     * @param int $id
     * @return mixed
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @param int $mobile
     * @return mixed
     */
    public function setMobile(int $mobile)
    {
       $this->mobile=$mobile;
    }

    /**
     * @param int $code
     * @return mixed
     */
    public function setAuthCode(int $code)
    {
        $this->authCode=$code;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getMobile(): int
    {
        return $this->mobile;
    }

    /**
     * @return int
     */
    public function getAuthCode(): int
    {
        return $this->authCode;
    }
}
