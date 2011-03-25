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

    public function defineComponent ($pId)
    {
        if (!is_string($pId)) {
            throw new ContainerException('');
        }
        if (! array_key_exists($pId, $this->_componentDefinitions)) {
            return $this->_componentDefinitions[$pId] = new ContainerComponent($pId, $this);
        } else {
            return $this->_componentDefinitions[$pId];
        }
    }

    public function get ($pId)
    {
        if (! array_key_exists($pId, $this->_componentDefinitions)) {
            throw new ContextException('Unknown component '.$pId);
        }

        if ($this->_componentDefinition[$pId]->isShared()) {
            
        }

        return $this->_componentDefinitions[$pId];
    }
    
    public function _create (ContainerComponentDefinition $pDefinition)
    {
        $reflection = new \ReflectionClass($pDefinition->getClass());
        if ($pDefinition->hasMethod('__construct')){
            $args = array();
            foreach ($pDefinition->getConstructor() as $paramName=>$paramValue) {
                if ($paramValue instanceof Closure) {
                    $paramValue = call_user_func($paramValue);
                }
                $args[] = $paramValue;
            }
            //First instance
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
            $reflectionProperty->setValue($object, $value);

        }
        
        //Calling methods
        foreach ($pDefinition->getMethods() as $methodName=>$parameters) {
            foreach ($parameters as $paramName=>$paramValue) {
                if ($paramValue instanceof Closure) {
                    $paramValue = call_user_func($paramValue);
                }
                $args[] = $paramValue;
            }
            $reflectionMethod = $reflection->getMethod($methodName);
            if (count($args)) {
                $reflectionMethod->invoke();
            } else {
                $reflectionMethod->invokeArgs();
            }
        }

        //Calling the configurator
        return $object;
    }    
}