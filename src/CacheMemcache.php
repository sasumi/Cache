<?php
namespace LFPhp\Cache;

use Exception;
use Memcache as SysMem;

class CacheMemcache extends CacheAdapter {
	/** @var SysMem * */
	private $cache;
	private $defaultHost = '127.0.0.1'; //default host name
	private $defaultPort = 11211;       //default port

	public function __construct(array $config){
		if(!extension_loaded('memcache')){
			throw new Exception('Can not find the memcache extension', 403);
		}

		$servers = $config['servers'];
		$this->cache = new SysMem;
		if(!empty($servers)){
			foreach($servers as $server){
				$this->addServer($server);
			}
		}else{
			$this->addServer($this->defaultHost.':'.$this->defaultPort);
		}
		parent::__construct($config);
	}

	/**
	 * @brief  Adding a server to the connection pool
	 * @param string $address add server
	 * @throws \Exception
	 */
	private function addServer($address){
		list($host, $port) = explode(':', $address);
		$port = $port ?: $this->defaultPort;
		if(!$this->cache->addserver($host, $port)){
			throw new Exception('Add server fail:'.$host);
		}
	}

	/**
	 * @brief Write to cache
	 * @param string $cache_key Unique key value of cache
	 * @param mixed $data Cache data to be written
	 * @param int $expired Cache data expiration time, unit: seconds
	 * @return bool true: success; false: failure;
	 */
	public function set($cache_key, $data, $expired = 0){
		return $this->cache->set($cache_key, $data, MEMCACHE_COMPRESSED, $expired);
	}

	/**
	 * @brief Read cache
	 * @param string $cache_key Unique key value of cache, can be written as an array when multiple values are to be returned
	 * @return array|false|string Cache data read out; null: no data is obtained;
	 */
	public function get($cache_key){
		return $this->cache->get($cache_key);
	}

	/**
	 * @brief Delete cache
	 * @param string $cache_key Unique key value of cache
	 * @param int|string $timeout Automatically delete in interval unit time, unit: seconds
	 */
	public function delete($cache_key, $timeout = 0){
		$this->cache->delete($cache_key, $timeout);
	}

	/**
	 * @brief Delete all caches
	 */
	public function flush(){
		$this->cache->flush();
	}
}
