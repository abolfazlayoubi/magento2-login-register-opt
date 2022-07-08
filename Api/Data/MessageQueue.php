<?php


namespace Magesoft\Otp\Api\Data;


interface MessageQueue
{

    /**
     * @param int $id
     * @return mixed
     */
    public function setId(int $id);

    /**
     * @param int $mobile
     * @return mixed
     */
    public function setMobile(int $mobile);

    /**
     * @param int $code
     * @return mixed
     */
    public function setAuthCode(int $code);


    /**
     * @return int
     */
    public function getId():int;

    /**
     * @return int
     */
    public function getMobile():int;

    /**
     * @return int
     */
    public function getAuthCode():int;
}
