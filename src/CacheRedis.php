<?php
namespace LFPhp\Cache;

use Exception;
use Redis as SysRedis;

class CacheRedis extends CacheAdapter{
	/** @var \Redis */
	private $redis = null;              //缓存对象
	private $defaultHost = '127.0.0.1'; //默认服务器地址
	private $defaultPort = 6379;        //默认端口号
	private $queueName = 'redis_queue';

	protected function __construct(array $config){
		if(!extension_loaded('redis')){
			throw new Exception('No redis extension found');
		}
		parent::__construct($config);
		$server = $config ?: [
			'host'     => $this->defaultHost,
			'port'     => $this->defaultPort,
			'database' => '',
			'password' => '',
		];
		$this->redis = new SysRedis();
		$this->redis->connect($server['host'], $server['port']);
		if($server['password']){
			$this->redis->auth($server['password']);
		}
		if($server['database']){
			$this->select($server['database']);
		}
	}

	/**
	 * @param $db_index
	 * @return bool
	 */
	public function select($db_index){
		return $this->redis->select($db_index);
	}

	/**
	 * swap database
	 * @param $from_db_index
	 * @param $to_db_index
	 * @return bool
	 */
	public function swapDb($from_db_index, $to_db_index){
		return $this->redis->swapdb($from_db_index, $to_db_index);
	}

	/**
	 * set cache
	 * @param $cache_key
	 * @param $data
	 * @param int $expired
	 * @return bool|mixed
	 */
	public function set($cache_key, $data, $expired = 60){
		$data = serialize($data);
		return $this->redis->setex($cache_key, $expired, $data);
	}

	/**
	 * get data
	 * @param $cache_key
	 * @return mixed|null
	 */
	public function get($cache_key){
		$data = $this->redis->get($cache_key);
		return $data === false ? null : unserialize($data);
	}

	/**
	 * @param $cache_key
	 * @return mixed|void
	 */
	public function delete($cache_key){
		$this->redis->del($cache_key);
	}

	/**
	 * @return bool|mixed
	 */
	public function flush(){
		return $this->redis->flushAll();
	}

	/**
	 * 设置队列名称
	 * @param $queueName
	 * @return \LFPhp\Cache\CacheRedis
	 */
	public function setQueueName($queueName){
		$this->queueName = $queueName;
		return $this;
	}

	/**
	 * 取得队列的长度
	 */
	public function lSize(){
		return $this->redis->lLen($this->queueName);
	}

	/**
	 * 从队列中取出多少个数据
	 * @param $num
	 * @return mixed
	 */
	public function lRang($num){
		return $this->redis->lRange($this->queueName, 0, $num);
	}

	/**
	 * 给队列添加一个数据
	 * @param $value
	 * @return bool|int
	 */
	public function rPush($value){
		return $this->redis->rPush($this->queueName, $value);
	}

	/**
	 * 从队列中取出一个数据
	 */
	public function lPop(){
		return $this->redis->lPop($this->queueName);
	}

	/**
	 * 从队列中删除数据
	 * @param number $start 开始index
	 * @param number $stop 结束index
	 * @return mixed
	 */
	public function lTrim($start, $stop){
		return $this->redis->lTrim($this->queueName, $start, $stop);
	}
}
