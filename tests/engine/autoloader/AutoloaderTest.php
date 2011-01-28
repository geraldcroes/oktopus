<?php 
class AutoloaderTest extends PHPUnit_Framework_TestCase {
	public function setUp (){
		require ('./Oktopus/Engine.class.php');
		require ('./Oktopus/engine/autoloader/Autoloader.class.php');
		require ('./Oktopus/engine/exception/Exception.class.php');
		require ('./Oktopus/engine/autoloader/AutoloaderException.class.php');
	}
	public function testAutoloaderInstance (){
		Oktopus\Autoloader::instance ();
	}
}
