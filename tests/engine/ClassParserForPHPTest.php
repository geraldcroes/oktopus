<?php
use Oktopus\ClassParserForPHP5_3;

class ClassParserForPHPTest extends PHPUnit_Framework_TestCase {
	public function setUp (){
		include (__DIR__.'/../bootstrap.php');
	}
	
	public function testSimpleFile (){
		$parser = new ClassParserForPHP5_3();
		$this->assertEquals (array ('foo'), $parser->find (__DIR__.'/../resources/nowarning/foo.php'));
		$this->assertEquals (array ('foo'), $parser->find (__DIR__.'/../resources/nowarning/foo.php'));//twice
		
		$this->assertEquals (array (), $parser->find (__DIR__.'/../resources/nowarning/empty/empty.php'));
		$this->assertEquals (array (), $parser->find (__DIR__.'/../resources/nowarning/empty/empty2.php'));
		
		$this->assertEquals (array ('foo2', 'foo3'), $parser->find (__DIR__.'/../resources/nowarning/foo2andfoo3.php'));
	}
	
	public function testNameSpace (){
		$parser = new ClassParserForPHP5_3();
		$this->assertEquals (array ('foo\\foo', 'foofoo'), $parser->find (__DIR__.'/../resources/nowarning/namespaces/namespaces.php'));

		$this->assertEquals (array ('foo2\\foo', 'foo2\\foo2', 'foo3\\foo'), $parser->find (__DIR__.'/../resources/nowarning/namespaces/namespaces2.php'));
	}
}
