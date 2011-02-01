<?php
use Oktopus\Autoloader;
use Oktopus\ClassParserForPHP5_3;
use Oktopus\Engine;

class AutoloaderMovedJustCheckTest extends PHPUnit_Framework_TestCase {
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
		exec('mv /tmp/OktopusTest/testDeleteFile/sources/foo.php /tmp/OktopusTest/testDeleteFile/sources/foomoved.php');
		$this->assertTrue($autoloader->autoload('foo', true));
	}	
}