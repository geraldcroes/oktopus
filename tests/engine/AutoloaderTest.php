<?php
use Oktopus\Autoloader;
use Oktopus\ClassParserForPHP5_3;
use Oktopus\Engine;

class AutoloaderTest extends PHPUnit_Framework_TestCase {
	public function testAutoloaderWarningTwoSameClassesTwoFile (){
		$autoloader = new Autoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/warning2files');
	
		$error_handler = $this->getMock ('ErrorHandler', array ('error_handler'));
		$error_handler->expects ($this->atLeastOnce ())->method ('error_handler');
		set_error_handler (array($error_handler, 'error_handler'));
		$this->assertTrue ($autoloader->autoload ('Afoo'));
		restore_error_handler ();
	}
	
	public function testAutoloaderWarningTwoSameNamespaceClassesTwoFile (){
		$autoloader = new AUtoloader (null, new ClassParserForPHP5_3());
		$autoloader->addPath(__DIR__.'/../resources/warningnamespace2files');
		
		$error_handler = $this->getMock ('ErrorHandler', array ('error_handler'));
		$error_handler->expects ($this->atLeastOnce ())->method ('error_handler');
		set_error_handler (array($error_handler, 'error_handler'));
		$autoloader->autoload ('not_exists');
		restore_error_handler ();
	}
}