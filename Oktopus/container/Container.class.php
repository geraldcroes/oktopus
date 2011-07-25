<?php
namespace Oktopus;

use \Closure;


/**
 * Interface for Oktopus Containers
 * @package Oktopus
 */
interface IContainer
{
    public function get ($pId);
    public function hasComponent($pId);
}

/**
 * Interface for containers where you can add / update definitions
 * @package Oktopus
 */
interface IMutableContainer extends IContainer
{
    public function define ($pId, $pClassName = null);
    public function getDefinition ($pId);
}

/**
 * Base Exception class for container operations
 * @package Oktopus
 */
class ContainerException extends \Exception
{
}

/**
 * Container
 * @package Oktopus
 */
class Container implements IMutableContainer
{
    private $_componentDefinitions = array();
    
    private $_components = array();

    public function define ($pId, $pClassName = null)
    {
        if (array_key_exists($pId, $this->_componentDefinitions)) {
            throw new ContainerException("The $pId Component is already defined");
        }

        $this->_componentDefinitions[$pId] = new ComponentDefinition($pId);
        return $this->_componentDefinitions[$pId]->setClass($pClassName === null ? $pId : $pClassName);
    }

    public function getDefinition ($pId)
    {
        if (! array_key_exists($pId, $this->_componentDefinitions)) {
            throw new ContainerException('Unknown component '.$pId);
        }
        return $this->_componentDefinitions[$pId];
    }

    public function get ($pId)
    {
        $definition = $this->getDefinition($pId);

        if ($definition->isShared()) {
        	if (isset($this->_components[$pId])) {
        		return $this->_components[$pId];
        	}
        }

        $toReturn = $this->_create($definition);
        if ($definition->isShared()) {
        	$this->_components[$pId] = $toReturn;
        }
        return $toReturn;
    }

    public function hasComponent ($pId) 
    {
        return array_key_exists($pId, $this->_componentDefinitions);
    }
    
    public function _create (ComponentDefinition $pDefinition)
    {
        //TODO : We should try / catch the method calls and properties
        //       to avoid a __destruct call in the constructed object
        //       destruction that could makes use of the injected 
        //       properties & elements.
        
        if ($pDefinition->hasFactory()) {
        	$args = array();
        	$factory = $pDefinition->getFactory();
        	foreach ($factory[1] as $paramName=>$paramValue) {
                if ($paramValue instanceof Closure) {
                    $paramValue = call_user_func($paramValue);
                } elseif ($paramValue instanceof ComponentReference) {
                    if ($paramValue->getContainer() !== null) {
                        $containerConstructor = $paramValue->getContainer();
                    } else {
                        $containerConstructor = $this;
                    }
                    $paramValue = $containerConstructor->get($paramValue->getId());
                }
                $args[] = $paramValue;
        	}

        	if (count($args)) {
        		$object = call_user_func_array($factory[0], $args);
        	} else {
        		$object = call_user_func($factory[0]);
        	}
        } elseif ($pDefinition->hasConstructorArguments()) {
            $args = array();
            foreach ($pDefinition->getConstructorArguments() as $paramName=>$paramValue) {
                if ($paramValue instanceof Closure) {
                    $paramValue = call_user_func($paramValue);
                } elseif ($paramValue instanceof ComponentReference) {
                    if ($paramValue->getContainer() !== null) {
                        $containerConstructor = $paramValue->getContainer();
                    } else {
                        $containerConstructor = $this;
                    }
                    $paramValue = $containerConstructor->get($paramValue->getId());
                }
                $args[] = $paramValue;
            }
            $reflection = new \ReflectionClass($pDefinition->getClass());
            $object = $reflection->newInstanceArgs($args);
        } else {
            $reflection = new \ReflectionClass($pDefinition->getClass());
            $object = $reflection->newInstance();
        }
        //injecting private properties
        foreach ($pDefinition->getProperties() as $name=>$value) {
            $reflectionProperty = new \ReflectionProperty($pDefinition->getClass(), $name);
            if (! $reflectionProperty->isPublic()){
                $reflectionProperty->setAccessible(true);
            }

            if ($value instanceof Closure) {
                $value = $value();
            } elseif ($value instanceof ComponentReference) {
                if ($value->getContainer() !== null) {
                    $containerConstructor = $value->getContainer();
                } else {
                    $containerConstructor = $this;
                }
                $value = $containerConstructor->get($value->getId());
            }
            $reflectionProperty->setValue($object, $value);
        }
        
        //Calling methods
        foreach ($pDefinition->getMethods() as $methodName=>$parameters) {
        	$args = array();
            foreach ($parameters as $paramName=>$paramValue) {
                if ($paramValue instanceof Closure) {
                    $paramValue = call_user_func($paramValue);
                } elseif ($paramValue instanceof ComponentReference) {
                    if ($paramValue->getContainer() !== null) {
                        $containerConstructor = $paramValue->getContainer();
                    } else {
                        $containerConstructor = $this;
                    }
                    $paramValue = $containerConstructor->get($paramValue->getId());
                }
                $args[] = $paramValue;
            }
            $reflectionMethod = new \ReflectionMethod($pDefinition->getClass(), $methodName);
            if (count($args) === 0) {
                $reflectionMethod->invoke($object);
            } else {
                $reflectionMethod->invokeArgs($object, $args);
            }
        }

        //Calling the configurator
        return $object;
    }    
}