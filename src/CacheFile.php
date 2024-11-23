<?php
namespace LFPhp\Cache;

/**
 * File cache
 * Default cache is in the system temporary directory
 * Default in-process variable cache is enabled to avoid multiple access to variable read files config:cache_in_process
 * @package LFPhp\Cache
 */
class CacheFile extends CacheAdapter{
	private $cache_in_process = true;
	private static $process_cache = [];

	protected function __construct(array $config = []){
		if(!isset($config['cache_in_process'])){
			$this->cache_in_process = true;
		}
		if(!isset($config['dir']) || !$config['dir']){
			$dir = sys_get_temp_dir();
			$config['dir'] = $dir.'/cache';
		}
		if(!is_dir($config['dir'])){
			mkdir($config['dir'], 0777, true);;
		}
		parent::__construct($config);
	}

	/**
	 * set cache
	 * @param $cache_key
	 * @param $data
	 * @param int $expired
	 * @return false|int
	 */
	public function set($cache_key, $data, $expired = 60){
		$file = $this->getFileName($cache_key);
		$string = json_encode(array(
			'cache_key' => $cache_key,
			'expired'   => date('Y-m-d H:i:s', time()+$expired),
			'data'      => $data,
		));
		if($handle = fopen($file, 'w')){
			$result = fwrite($handle, $string);
			fclose($handle);
			if($result && $this->cache_in_process){
				self::$process_cache[$cache_key] = $data;
			}
			return $result;
		}
		return false;
	}

	/**
	 * get cache file name
	 * @param $cache_key
	 * @return string
	 */
	public function getFileName($cache_key){
		return $this->getConfig('dir').'/'.md5($cache_key).'.json';
	}

	/**
	 * get cache
	 * @param $cache_key
	 * @return null
	 */
	public function get($cache_key){
		if($this->cache_in_process && isset(self::$process_cache[$cache_key])){
			return self::$process_cache[$cache_key];
		}
		$file = $this->getFileName($cache_key);
		if(file_exists($file)){
			$string = file_get_contents($file);
			if($string){
				$data = json_decode($string, true);
				if($data && strtotime($data['expired'])>time()){
					if($this->cache_in_process){
						self::$process_cache[$cache_key] = $data['data'];
					}
					return $data['data'];
				}
			}
			//清空无效缓存，防止缓存文件膨胀
			$this->delete($cache_key);
		}
		return null;
	}

	/**
	 * delete cache
	 * @param $cache_key
	 * @return bool
	 */
	public function delete($cache_key){
		if(isset(self::$process_cache[$cache_key])){
			unset(self::$process_cache[$cache_key]);
		}
		$file = $this->getFileName($cache_key);
		if(file_exists($file)){
			return unlink($file);
		}
		return false;
	}

	/**
	 * flush cache
	 * flush cache dir
	 */
	public function flush(){
		self::$process_cache = [];
		$dir = $this->getConfig('dir');
		if(is_dir($dir)){
			array_map('unlink', glob($dir.'/*'));
		}
	}
}
