<?php
use Oktopus\ClassParserForPHP5_3;

class ClassParserForPHPTest extends PHPUnit_Framework_TestCase {
	public function testSimpleFile (){
		$parser = new ClassParserForPHP5_3();
		$this->assertEquals (array ('foo'), $parser->find (__DIR__.'/resources/foo.php'));
		$this->assertEquals (array ('foo'), $parser->find (__DIR__.'/resources/foo.php'));//twice
		
		$this->assertEquals (array (), $parser->find (__DIR__.'/resources/empty.php'));
		$this->assertEquals (array (), $parser->find (__DIR__.'/resources/empty2.php'));
		
		$this->assertEquals (array ('foo', 'foo2'), $parser->find (__DIR__.'/resources/fooandfoo2.php'));
	}
	
	public function testNameSpace (){
		$parser = new ClassParserForPHP5_3();
		$this->assertEquals (array ('foo\\foo', 'foofoo'), $parser->find (__DIR__.'/resources/namespaces.php'));		
	}
}