<?php

namespace Magesoft\Otp\Model\Redis;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magesoft\Otp\Model\Redis\RedisStorageDataStorage;
class RedisStorageDataStorageLink extends AbstractExtensibleModel implements RedisStorageDataStorage
{
    public function setType(string $type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    public function setValue(array $value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }

    public function getValue()
    {
        return $this->getData(self::KEY_VALUE);
    }

    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }



    public function getData($key = '', $index = null)
    {
        if (!empty($key)) {
            return $this->_data[$key]??"";
        }
        return [
          self::KEY_TYPE=>$this->getType(),
          self::KEY_VALUE=>$this->getValue(),
          self::KEY_LIFE_TIME=>$this->getLifeTime(),
          self::KEY_NAME=>$this->getName()
        ];
    }

    public function setLifeTime(int $time)
    {
        return $this->setData(self::KEY_LIFE_TIME, $time);
    }

    public function getLifeTime()
    {
        return intval($this->getData(self::KEY_LIFE_TIME));
    }

    public function setName(string $name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }
}
