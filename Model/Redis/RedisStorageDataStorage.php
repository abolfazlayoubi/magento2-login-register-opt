<?php


namespace Magesoft\Otp\Model\Redis;


interface RedisStorageDataStorage
{
    const KEY_TYPE='type';
    const KEY_VALUE='value';
    const KEY_LIFE_TIME='life_time';
    const KEY_NAME="name";

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type);

    /**
     * @param array $value
     * @return $this
     */
    public function setValue(array $value);


    /**
     * @param int $time
     * @return $this
     */
    public function setLifeTime(int $time);

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return mixed
     */
    public function getType();

    /**
     * @return int
     */
    public function getLifeTime();

    /**
     * @return string
     */
    public function getName();
}
