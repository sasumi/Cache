<?php
namespace LFPhp\Cache;

/**
 * 运行时内存变量缓存（进程内共享）
 * Class CacheVar
 * @package LFPhp\Cache
 */
class CacheVar extends CacheAdapter {
	private static $DATA_STORE = array();
	private $cache_key_prefix = '_var_cache_';

	private function getCacheKey($cache_key){
		return $this->cache_key_prefix.$cache_key;
	}

	public function set($cache_key, $data, $_=0){
		$cache_key = $this->getCacheKey($cache_key);
		self::$DATA_STORE[$cache_key] = $data;
	}

	public function get($cache_key){
		$cache_key = $this->getCacheKey($cache_key);
		return isset(self::$DATA_STORE[$cache_key]) ? self::$DATA_STORE[$cache_key] : null;
	}

	public function delete($cache_key){
		$cache_key = $this->getCacheKey($cache_key);
		self::$DATA_STORE[$cache_key] = null;
		return true;
	}

	public function flush(){
		self::$DATA_STORE = array();
	}

	public function getAll(){
		return self::$DATA_STORE;
	}
}
