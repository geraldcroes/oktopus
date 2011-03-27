<?php
namespace Oktopus;

/**
 * Base Exception class for container operations
 */
class ContainerException extends \Exception
{
}

/**
 * Container
 */
class Container
{
    private $_componentDefinitions = array();
    
    private $_components = array();

    public function define ($pId)
    {
        if (! array_key_exists($pId, $this->_componentDefinitions)) {
            return $this->_componentDefinitions[$pId] = new ContainerComponentDefinition($pId);
        } else {
            return $this->_componentDefinitions[$pId];
        }
    }

    public function get ($pId)
    {
        if (! array_key_exists($pId, $this->_componentDefinitions)) {
            throw new ContainerException('Unknown component '.$pId);
        }

        if ($this->_componentDefinitions[$pId]->isShared()) {
        	if (isset($this->_components[$pId])) {
        		return $this->_components[$pId];
        	}
        }

        $toReturn = $this->_create($this->_componentDefinitions[$pId]);
        if ($this->_componentDefinitions[$pId]->isShared()) {
        	$this->_components[$pId] = $toReturn;
        }
        return $toReturn;
    }
    
    public function _create (ContainerComponentDefinition $pDefinition)
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