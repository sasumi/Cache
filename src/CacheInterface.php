<?php
namespace LFPhp\Cache;

/**
 * Cache Interface
*/
interface CacheInterface {
	/**
	 * Set cache
	 * @param $cache_key
	 * @param $data
	 * @param int $expired
	 * @return mixed
	 */
	public function set($cache_key, $data, $expired=60);

	/**
	 * Get cache
	 * @param $cache_key
	 * @return mixed
	 */
	public function get($cache_key);

	/**
	 * Delete cache
	 * @param $cache_key
	 */
	public function delete($cache_key);

	/**
	 * Flush cache
	 * @return mixed
	 */
	public function flush();
}
