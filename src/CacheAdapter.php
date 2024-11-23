<?php
namespace LFPhp\Cache;

use Exception;

/**
 * Cache Adapter
 * @package LFPhp\Cache
 */
abstract class CacheAdapter implements CacheInterface{
	private static $instances;
	private $config;

	/**
	 * @param array $config
	 */
	protected function __construct($config = []){
		$this->setConfig($config);
	}

	/**
	 * Singleton
	 * @param array $config
	 * @return static
	 */
	final public static function instance(array $config = array()){
		$class = get_called_class();
		$key = $class.serialize($config);
		if(!isset(self::$instances[$key]) || !self::$instances[$key]){
			self::$instances[$key] = new $class($config);
		}
		return self::$instances[$key];
	}

	/**
	 * Quick call method, no configuration parameters are provided
	 * @param string $key Cache key
	 * @param callable $fetcher Data acquisition callback, callback returns null, which is invalid data and is not stored in the cache
	 * @param int $expired_seconds Cache expiration time
	 * @param bool $refresh_cache Whether to refresh the cache, the default false is to update only when the cache expires
	 * @return mixed
	 * @throws \Exception
	 */
	final public function cache($key, callable $fetcher, $expired_seconds = 60, $refresh_cache = false){
		$cache_class = get_called_class();
		if($cache_class == self::class){
			throw new Exception('Cache method not callable in '.self::class);
		}

		if($refresh_cache){
			$data = call_user_func($fetcher);
			isset($data) && $this->set($key, $data, $expired_seconds);
			return $data;
		}

		$data = $this->get($key);
		if(!isset($data)){
			$data = call_user_func($fetcher);
			isset($data) && $this->set($key, $data, $expired_seconds);
		}
		return $data;
	}

	/**
	 * Distributed cache storage
	 * @param $cache_prefix_key
	 * @param array $data_list
	 * @param int $expired
	 */
	final public function setDistributed($cache_prefix_key, array $data_list, $expired = 60){
		foreach($data_list as $k=>$data){
			$this->set($cache_prefix_key.$k, $data, $expired);
		}
	}

	/**
	 * Get config
	 * @param string $key
	 * @return mixed
	 */
	public function getConfig($key = ''){
		if($key){
			return $this->config[$key];
		}
		return $this->config;
	}

	/**
	 * Set config
	 * @param $config
	 */
	public function setConfig($config){
		$this->config = $config;
	}
}
