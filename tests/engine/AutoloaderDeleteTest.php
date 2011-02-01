<?php
use Oktopus\Autoloader;
use Oktopus\ClassParserForPHP5_3;
use Oktopus\Engine;

class AutoloaderDeleteTest extends PHPUnit_Framework_TestCase {
	public function testCannotWriteInCacheFile (){
		//Remove old cache file if it exists and copy sources to test
		exec('rm -Rf /tmp/OktopusTest/testDeleteFile/tmp/');
		exec('rm -Rf /tmp/OktopusTest/testDeleteFile/sources/');
		exec('cp -R '.__DIR__.'/resources/nowarning /tmp/OktopusTest/testDeleteFile/sources/');
		
		$autoloader = new Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$this->assertTrue($autoloader->autoload ('foo2'));//wil generate cachefile for every classes.
		
		$autoloader = new Autoloader('/tmp/OktopusTest/testDeleteFile/tmp/', new ClassParserForPHP5_3());
		$autoloader->addPath('/tmp/OktopusTest/testDeleteFile/sources/');
		$autoloader->autoload('foo2');//to include the cache and tells where foo should also be founded.
		exec('rm /tmp/OktopusTest/testDeleteFile/sources/foo.php');
		$this->assertFalse($autoloader->autoload('foo'));
	}	
}