<?php
namespace LFPhp\Cache\TestCase;
use LFPhp\Cache\CacheFile;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase {
	public function testFile(){
		CacheFile::instance()->set('hello', 'a');
		$var = CacheFile::instance()->get('hello');
		$this->assertEquals($var, 'a');
	}
}