<?php

namespace Magesoft\Otp\Model;


use Magesoft\Otp\Model\Redis\RedisStorageDataStorage;
use Magento\Framework\App\Config\ScopeConfigInterface as Config;

class RedisStorage
{
    /**
     * @var \Redis
     */
    private $redisClient;

    /**
     * @var RedisStorageDataStorage
     */
    public RedisStorageDataStorage $dataStorage;

    /**
     * Storage constructor.
     * @param Config $config
     * @param \Redis $redis
     * @param RedisStorageDataStorage $dataStorage
     */
    public function __construct(
        Config $config,
        \Redis $redis,
        RedisStorageDataStorage $dataStorage
    ) {
        if ($this->redisClient === null) {
            $server = $config->getValue('rds_core_base_settings/redis_storage/server');
            if (!empty($server)) {
                [$host, $port] = explode(':', $server);
                $redis->connect($host, $port);

                $dbIndex = (int)$config->getValue('rds_core_base_settings/redis_storage/db');
                $redis->select($dbIndex);
            }

            $this->redisClient = $redis;
        }
        $this->dataStorage=$dataStorage;
    }

    /**
     * @return \Redis
     */
    public function getRedisClient(): \Redis
    {
        return $this->redisClient;
    }

    /**
     * @param string $key
     * @param RedisStorageDataStorage $dataStorage
     */
    public function setData(string $key, RedisStorageDataStorage $dataStorage)
    {
        $redis = $this->getRedisClient();
        $redis->setex($key, $dataStorage->getLifeTime(), json_encode($dataStorage->getData()));
    }

    /**
     * @param string $key
     * @param RedisStorageDataStorage $dataStorage
     */
    public function setForeverData(string $key, RedisStorageDataStorage $dataStorage)
    {
        $redis = $this->getRedisClient();
        $redis->set($key . "_" . $dataStorage->getName(), json_encode($dataStorage->getData()));
    }

    /**
     * @param string|array $key
     * @return array
     */
    public function getData($key)
    {
        $redis = $this->getRedisClient();
        if (getType($key)=='array') {
            $result=[];
            foreach ($key as $item) {
                $result[]=$this->getData($item);
            }
            return $result;
        }
        return json_decode($redis->get($key), true);
    }

    public function deleteByKey($key)
    {
        $redis = $this->getRedisClient();
        $redis->del($key);
    }
    /**
     * @return RedisStorageDataStorage
     */
    public function getDataStorageClass()
    {
        return $this->dataStorage;
    }

    /**
     * Execute a given function on every key of the matched pattern
     * @param string|null $pattern
     * @param callable $callback
     */
    public function redisKeyWalk(?string $pattern)
    {
        $redis = $this->getRedisClient();

        $iterator = null;
        return $redis->scan($iterator, $pattern);
    }
}
