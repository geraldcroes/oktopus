<?php
class ContainerXMLLoaderTest extends PHPUnit_Framework_TestCase
{
	public function setUp ()
	{
		require_once __DIR__.'/../bootstrap.php';
		Oktopus\Engine::autoloader()->addPath(__DIR__.'/../resources/container/');
	}

    public function testBasicLoading ()
    {
        $nakedContainer = new Oktopus\Container();
        $container = new Oktopus\ContainerXMLLoader($nakedContainer);
        
        $container->addXmlFile(__DIR__.'/../resources/container/basics.xml');
        $this->assertFalse($container->hasComponent('notInContainer'));
        $this->assertTrue($container->hasComponent('Fruit'));
        $this->assertTrue($container->hasComponent('Apple'));
        $this->assertTrue($container->hasComponent('Juicer'));
    }
}