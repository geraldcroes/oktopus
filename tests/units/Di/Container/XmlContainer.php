<?php
namespace Oktopus\Di\Container\tests\units;

require_once __DIR__ . '/../../../bootstrap.php';

use \mageekguy\atoum;
use \Oktopus;

Oktopus\Engine::autoloader()->addPath(__DIR__ . '/../../../resources/container/');

class XmlContainer extends atoum\test
{
    public function testBasicLoading()
    {
        $container = new Oktopus\Di\Container\XmlContainer(__DIR__ . '/../../../resources/container/basics.xml');

        $this->assert->boolean($container->hasComponent('notInContainer'))->isFalse();
        $this->assert->boolean($container->hasComponent('Fruit'))->isTrue();
        $this->assert->boolean($container->hasComponent('Apple'))->isTrue();
        $this->assert->boolean($container->hasComponent('Juicer'))->isTrue();

        //creating objects
        $apple = $container->get('Apple');
        $juicer = $container->get('Juicer');
        $tool = $container->get('Tool');
        $this->assert->object($apple)->isIdenticalTo($juicer->getFruit());
        $this->assert->object($juicer->getTool())->isIdenticalTo($tool);

        //creating unshared components
        $fruit = $container->get('Fruit');
        $fruit2 = $container->get('Fruit');
        $this->assert->object($fruit)->isNotIdenticalTo($fruit2);

        //creating object with reference configured with attributes and tags
        $juicer1 = $container->get('Juicer1');
        $this->assert->object($juicer1)->isNotIdenticalTo($juicer);
        $this->assert->object($juicer1->getFruit())->isIdenticalTo($apple);
        $this->assert->object($juicer1->getTool())->isIdenticalTo($tool);

        //creating an object with a simple value as its property
        $easyPrivate = $container->get('EasyPrivate');
        $this->assert->string($easyPrivate->getProperty())->isEqualTo('propertyValue');

        //creating an object with a simple value as its property declared in a tag
        $easyPrivate1 = $container->get('EasyPrivate1');
        $this->assert->string($easyPrivate1->getProperty())->isEqualTo('propertyValue');

        //creating an object with a simple empty value as its property declared in an attribute
        $easyPrivate2 = $container->get('EasyPrivate2');
        $this->assert->string($easyPrivate2->getProperty())->isEqualTo('');

        //creating an object with a simple empty value as its property declared in a tag
        $easyPrivate3 = $container->get('EasyPrivate3');
        $this->assert->string($easyPrivate3->getProperty())->isEqualTo('');

        //creating an object with a simple empty value as its property declared in a tag
        $easyPrivate4 = $container->get('EasyPrivate4');
        $this->assert->variable($easyPrivate4->getProperty())->isNull();

        //creating an object with a constructor with one argument
        $constructedOneArguments = $container->get('ConstructedOneParameter');
        $this->assert->string($constructedOneArguments->getFirst())->isEqualTo('one1');

        //creating an object with a constructor with two argument
        $constructedTwoArguments = $container->get('ConstructedTwoParameter');
        $this->assert->string($constructedTwoArguments->getFirst())->isEqualTo('one2');
        $this->assert->string($constructedTwoArguments->getSecond())->isEqualTo('two2');

        //creating an object with a constructor with one argument and a method call and a property directly assigned
        $constructedOneArguments2 = $container->get('ConstructedOneParameter2');
        $this->assert->string($constructedOneArguments2->getFirst())->isEqualTo('one12');
        $this->assert->string($constructedOneArguments2->getMore())->isEqualTo('valueOfMore12');
        $this->assert->string($constructedOneArguments2->getMoreNoSetter())->isEqualTo('valueOfMoreNoSetter12');

        //creating an object with a constructor with two arguments and a method call and a property directly assigned
        $constructedTwoArguments2 = $container->get('ConstructedTwoParameter2');
        $this->assert->string($constructedTwoArguments2->getFirst())->isEqualTo('one22');
        $this->assert->string($constructedTwoArguments2->getSecond())->isEqualTo('two22');
        $this->assert->string($constructedTwoArguments2->getMore())->isEqualTo('valueOfMore22');
        $this->assert->string($constructedTwoArguments2->getMore2())->isEqualTo('valueOfMore222');

        //creating an object with a factory with no parameters
        $constructedWithFactoryConstructedNoParameter = $container->get('FactoryConstructedNoParameter');
        $this->assert->string($constructedWithFactoryConstructedNoParameter->getFirst())->isEqualTo('one');
        $this->assert->string($constructedWithFactoryConstructedNoParameter->getSecond())->isEqualTo('two');

        $constructedWithFactoryConstructedOneParameter = $container->get('FactoryConstructedOneParameter');
        $this->assert->string($constructedWithFactoryConstructedOneParameter->getFirst())->isEqualTo('one1');
        $this->assert->string($constructedWithFactoryConstructedOneParameter->getSecond())->isEqualTo('two1');

        $constructedWithFactoryConstructedTwoParameter = $container->get('FactoryConstructedTwoParameter');
        $this->assert->string($constructedWithFactoryConstructedTwoParameter->getFirst())->isEqualTo('one2');
        $this->assert->string($constructedWithFactoryConstructedTwoParameter->getSecond())->isEqualTo('two2');
    }

    public function testUnreadableFile()
    {
        //Trying to add a non existant XML file should raise an error
        $this->assert
            ->exception(function () {
                    $container = new Oktopus\Di\Container\XmlContainer(__DIR__ . '/../../../resources/container/fileDoesNotExists');
                })
                ->isInstanceOf('Oktopus\Di\Container\ContainerException');
    }

    public function testUnsetComponentId()
    {
        //Trying to load an XML file with a component that do not have an id should raise an exception
        $this->assert
            ->exception(function () {
                    $container = new Oktopus\Di\Container\XmlContainer(__DIR__ . '/../../../resources/container/fail_unset_component_id.xml');
                })
                ->isInstanceOf('Oktopus\Di\ComponentDefinitionException');
    }

    public function testUnsetPropertyName()
    {
        //Trying to load an XML file with a component that have a component with a missing property name should raise an exception
        $this->assert
            ->exception(function () {
                $container = new Oktopus\Di\Container\XmlContainer(__DIR__ . '/../../../resources/container/fail_unset_property_name.xml');
                })
                ->isInstanceOf('Oktopus\Di\ComponentDefinitionException');
    }

    public function testUnsetPropertyValue()
    {
        //Trying to load an XML file with a component that have a component with a missing property value should raise an exception
        $this->assert
            ->exception(function () {
                $container = new Oktopus\Di\Container\XmlContainer(__DIR__ . '/../../../resources/container/fail_unset_property_value.xml');
            })
                ->isInstanceOf('Oktopus\Di\ComponentDefinitionException');
    }

    public function testUnsetMethodName()
    {
        //Trying to load an XML file with a component that have a component with a missing method name should raise an exception
        $this->assert
            ->exception(function () {
                $container = new Oktopus\Di\Container\XmlContainer(__DIR__ . '/../../../resources/container/fail_unset_method_name.xml');
            })
                ->isInstanceOf('Oktopus\Di\ComponentDefinitionException');
    }

    public function testUnsetFactoryClassname()
    {
        //Trying to load an XML file with a component that have a component with a missing factory classname attribute should raise an exception
        $this->assert
            ->exception(function () {
                    $container = new Oktopus\Di\Container\XmlContainer(__DIR__ . '/../../../resources/container/fail_unset_factory_classname.xml');
                })
                ->isInstanceOf('Oktopus\Di\ComponentDefinitionException');
    }

    public function testUnsetFactoryMethod()
    {
        //Trying to load an XML file with a component that have a component with a missing factory method attribute should raise an exception
        $this->assert
            ->exception(function () {
                    $container = new Oktopus\Di\Container\XmlContainer(__DIR__ . '/../../../resources/container/fail_unset_factory_method.xml');
                })
                    ->isInstanceOf('Oktopus\Di\ComponentDefinitionException');
    }
}
