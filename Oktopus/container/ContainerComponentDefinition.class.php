<?php
namespace Oktopus;

/**
 * Base exception for container component definition exception
 */
class ContainerComponentDefinitionException extends \Exception
{
}

/**
 * Component definition
 * 
 * @author gcroes
 */
class ContainerComponentDefinition
{
    /**
     * The component definition id
     * 
     * @var string
     */
    private $_id;

    /**
     * The component class
     * 
     * @var string
     */
    private $_class = null;
    
    /**
     * The components properties
     * 
     * @var array
     */
    private $_properties = array();
    
    /**
     * The component methods 
     * 
     * @var array
     */
    private $_methods = array();
    
    /**
     * The constructor parameter
     * 
     * @var array
     */
    private $_constructorArguments = null;
    
    /**
     * The constructor method
     * 
     * @var array
     */
    private $_constructorMethod = null;
        
    /**
     * If the component is shared or not
     * 
     * @var boolean
     */
    private $_shared = true;
    
    /**
     * Construction of the definition
     * 
     * @param string $pId the id of the component
     */
    public function __construct ($pId)
    {
        $this->_id = $pId;
    }

    /**
     * Define the component class
     * 
     * @param string $pClass the class name
     * 
     * @return ContainerComponentDefinition
     */
    public function setClass ($pClass)
    {
    	$this->_assertClassNameIsString($pClass);
        $this->_class = $pClass;
        return $this;
    }
    /**
     * Get the class described in the componentdefinition
     *
     * @return string
     */
    public function getClass ()
    {
        if ($this->_class === null) {
        	throw new ContainerComponentDefinitionException('Class name is not set');
        }
    	return $this->_class;
    }

    /**
     * Defines the property value
     * 
     * @param string $pName the property name
     * @param mixed $pValuev the property value
     * 
     * @return ContainerComponentDefinition
     */
    public function setProperty ($pName, $pValue)
    {
    	$this->_assertPropertyNameIsString($pName);
        $this->_properties[$pName] = $pValue;
        return $this;
    }
    /**
     * Tells if the property is defined 
     * 
     * @param string $pName the property name
     * 
     *  @return boolean
     */
    public function hasProperty ($pName)
    {
        return array_key_exists($pName, $this->_properties);
    }
    /**
     * Gets the property value 
     * 
     * @param string $pName the name of the property
     * 
     * @return mixed
     */
    public function getProperty ($pName)
    {
        if ($this->hasProperty($pName)) {
            return $this->_properties[$pName];
        }
        throw new ContainerComponentDefinitionException("Property $pName is not defined");
    }
    /**
     * Get the configured properties
     * 
     * @return array
     */
    public function getProperties ()
    {
    	return $this->_properties;
    }
    /**
     * Defines a method to call after the construction of the object
     * 
     * @param string $pName the method name
     * @param array $pArgs the method arguments
     * 
     * @return ContainerComponentDefinition
     */
    public function setMethod ($pName, array $pArgs = array())
    {
        $this->_assertMethodNameIsString($pName);
        $this->_methods[$pName] = $pArgs;
        return $this;
    }
    /**
     * Tells if the method is defined
     * 
     * @param string $pName
     * 
     * @return boolean
     */
    public function hasMethod ($pName)
    {
        $this->_assertMethodNameIsString($pName);
        return array_key_exists($pName, $this->_methods);
    }
    /**
     * Gets the method parameters
     * 
     * @param string $pName the method name
     * 
     * @return array
     */
    public function getMethod ($pName)
    {
        if ($this->hasMethod($pName)) {
            return $this->_methods[$pName];
        }
        throw new ContainerComponentDefinitionException("Method $pName is not defined");
    }
    /**
     * Get the configured methods
     * 
     * @return array
     */
    public function getMethods ()
    {
    	return $this->_methods;
    }
	/**
	 * Gets the constructor parameters
	 * 
	 * @param array $pArgs the contructor parameters
	 * 
	 * @return ContainerComponentDefinition
	 */
    public function setConstructorArguments (array $pArgs = array())
    {
        $this->_constructorArguments = $pArgs;
        return $this;
    }
    /**
     * Tells if there is a defined constructor
     * 
     * @return boolean
     */
    public function hasConstructorArguments ()
    {
        return $this->_constructorArguments !== null;
    }
    /**
     * Gets the constructor parameters
     * 
     * @return array
     */
    public function getConstructorArguments()
    {
        return $this->_constructorArguments;
    }
    /**
     * 
     */
    public function setConstructorMethod ($pCallBack, array $pArgs = array())
    {
    	$this->_constructorMethod = array($pCallBack, $pArgs);
    }
    public function hasConstructorMethod ()
    {
    	return $this->_constructorMethod !== null;
    }
    public function getConstructorMethod()
    {
    	return $this->_constructorMethod;
    	
    }  
    
    /**
     * Tells if the component should be shared or not
     * 
     * @param mixed $pShared true - Singleton, false not shared 
     * 
     * @return ContainerComponentDefinition
     */
    public function setShared ($pShared)
    {
        $this->_shared = $pShared;
        return $this;
    }
    /**
     * Tells if the component is Shared
     * 
     * @return boolean
     */
    public function isShared ()
    {
        return $this->_shared !== false;
    }
    
    /**
     * Asserts that the given parameter is a string
     * 
     * @param string $pMethodName the method name to check
     * 
     * @throws ContainerComponentDefinitionException
     */
    private function _assertMethodNameIsString ($pMethodName)
    {
        if (!is_string($pMethodName)) {
            throw new ContainerComponentDefinitionException("Method names can only be Strings");
        }
    }
    
    /**
     * Asserts that the given parameter is a string
     * 
     * @param string $pMethodName the method name to check
     * 
     * @throws ContainerComponentDefinitionException
     */
    private function _assertPropertyNameIsString ($pPropertyName)
    {
        if (!is_string($pPropertyName)) {
            throw new ContainerComponentDefinitionException("Property names can only be Strings");
        }
    }

    /**
     * Asserts that the given parameter is a string
     * 
     * @param string $pMethodName the method name to check
     * 
     * @throws ContainerComponentDefinitionException
     */
    private function _assertClassNameIsString ($pClassName)
    {
        if (!is_string($pClassName)) {
            throw new ContainerComponentDefinitionException("Class names can only be Strings");
        }
    }
}