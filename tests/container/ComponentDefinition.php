<?php
namespace Oktopus\tests\units;

require __DIR__.'/../bootstrap.php';

use \mageekguy\atoum;
use \Oktopus;

class ComponentDefinition extends atoum\test
{
	public function testProperties ()
	{
		$cd = new Oktopus\ComponentDefinition('foo');

		$this->assert->boolean($cd->hasProperty('foo'))->isFalse();
		$return = $cd->setProperty('foo', 'value');

		$this->assert->boolean($cd->hasProperty('foo'))->isTrue();
		$this->assert->string($cd->getProperty('foo'))->isEqualTo('value');
		$this->assert->object($cd)->isIdenticalTo($return);

        //Trying to get an unset property should fail
        $this->assert->exception(function () use ($cd) {$cd->getProperty('foo2');})->isInstanceOf('\Oktopus\ComponentDefinitionException');

		//Trying to set a wrong property name should raise an eception
		$this->assert->exception(function () use ($cd) {$cd->setProperty(array(), 'fooValue');})->isInstanceOf('\Oktopus\ComponentDefinitionException');

        //testing getProperties
        $this->assert->phpArray($cd->getProperties())->isEqualTo(array('foo'=>'value'));
        $cd->setProperty('otherFoo', 'otherValue');
        $this->assert->phpArray($cd->getProperties())->isEqualTo(array('foo'=>'value', 'otherFoo'=>'otherValue'));
	}
	
	public function testMethod ()
	{
		//Testing with a single parameter method name
		$cd = new Oktopus\ComponentDefinition('foo');

		$this->assert->boolean($cd->hasMethod('foo'))->isFalse();
		$return = $cd->setMethod('foo', array('value'));

		$this->assert->object($cd)->isIdenticalTo($return);
		$this->assert->boolean($cd->hasMethod('foo'))->isTrue();
		$this->assert->phpArray($cd->getMethod('foo'))->isEqualTo(array('value'));

        $this->assert->exception(function () use ($cd) {$cd->getMethod('foo2');})->isInstanceOf('\Oktopus\ComponentDefinitionException');

		//Testing with no parameters
		$cd->setMethod('foo2');
		$this->assert->boolean($cd->hasMethod('foo2'))->isTrue();
		$this->assert->phpArray($cd->getMethod('foo2'))->isEqualTo(array());

		//Trying to set a wrong method name
        $this->assert->exception(function () use($cd){$cd->setMethod(array());})->isInstanceOf('\Oktopus\ComponentDefinitionException');

        //testing getMethods
        $this->assert->phpArray($cd->getMethods())->sizeOf(2);
	}

	public function testConstructor ()
	{
		$cd = new Oktopus\ComponentDefinition('foo');
		$this->assert->boolean($cd->hasConstructorArguments())->isFalse();
		$return = $cd->setConstructorArguments(array('value'));

        $this->assert->object($cd)->isIdenticalTo($return);
		$this->assert->boolean($cd->hasConstructorArguments())->isTrue();
		$this->assert->phpArray($cd->getConstructorArguments())->isEqualTo(array('value'));
	}
	
	public function testShared ()
	{
		$cd = new Oktopus\ComponentDefinition('foo');
		//default shared value is true
        $this->assert->boolean($cd->isShared())->isTrue();

		$return = $cd->setShared(false);
		$this->assert->object($cd)->isIdenticalTo($return);
		$this->assert->boolean($cd->isShared())->isFalse();
	}

	public function testClass ()
	{
		$cd = new Oktopus\ComponentDefinition('foo');
        //Getting the class if not set should raise an exception
        $this->assert->exception(function()use($cd){$cd->getClass();})->isInstanceOf('\Oktopus\ComponentDefinitionException');

		$return = $cd->setClass('UneClass');
		$this->assert->object($cd)->isIdenticalTo($return);

        $this->assert->string($cd->getClass())->isEqualTo('UneClass');
		
		//trying to set a incorrect classname
        $this->assert->exception(function()use($cd){$cd->setClass(array());})->isInstanceOf('Oktopus\ComponentDefinitionException');
	}

    public function testFactories()
    {
        $cd = new Oktopus\ComponentDefinition('foo');
        $this->assert->boolean($cd->hasFactory())->isFalse();
        $cd->setFactory(array('FooDi2Factory', 'getInstance'));
        $this->assert->boolean($cd->hasFactory())->isTrue();
        $this->assert->phpArray($cd->getFactory())->sizeOf(2);
    }
}