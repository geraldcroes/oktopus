<?php
namespace Oktopus;

/**
 * Interface for Oktopus Containers
 */
interface IContainer
{
    public function get ($pId);
    public function hasComponent($pId);
}

/**
 * Interface for containers where you can add / update definitions
 */
interface IMutableContainer extends IContainer
{
    public function define ($pId, $pClassName = null);
    public function getDefinition ($pId);
}

/**
 * Base Exception class for container operations
 */
class ContainerException extends \Exception
{
}

/**
 * Container
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
        $reflection = new \ReflectionClass($pDefinition->getClass());
        if ($pDefinition->hasFactory()) {
        	$args = array();
        	$factory = $pDefinition->getFactory();
        	foreach ($factory[1] as $paramName=>$paramValue) {
                if ($paramValue instanceof \Closure) {
                    $paramValue = call_user_func($paramValue);
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
                if ($paramValue instanceof \Closure) {
                    $paramValue = call_user_func($paramValue);
                }
                $args[] = $paramValue;
            }
            $object = $reflection->newInstanceArgs($args);
        } else {
            $object = $reflection->newInstance();
        }

        //injecting private properties
        foreach ($pDefinition->getProperties() as $name=>$value) {
            $reflectionProperty = $reflection->getProperty($name);
            if (! $reflectionProperty->isPublic()){
                $reflectionProperty->setAccessible(true);
            }
            $reflectionProperty->setValue($object, $value instanceof \Closure ? call_user_func($value): $value);
        }
        
        //Calling methods
        foreach ($pDefinition->getMethods() as $methodName=>$parameters) {
        	$args = array();
            foreach ($parameters as $paramName=>$paramValue) {
                if ($paramValue instanceof \Closure) {
                    $paramValue = call_user_func($paramValue);
                }
                $args[] = $paramValue;
            }
            $reflectionMethod = $reflection->getMethod($methodName);
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