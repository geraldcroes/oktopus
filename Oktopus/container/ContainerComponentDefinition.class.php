<?php
use Oktopus\ContainerComponentDefinition;
namespace Oktopus;

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
    private $_class;
    
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
        return $this->getClass();
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
        return array_key_exists(array_key_exists($pName, $this->_methods));
    }
    public function getMethod ($pName)
    {
        if ($this->hasMethod($pName)) {
            return $this->_properties[$pName];
        }
        throw new ContainerComponentDefinitionException("Method $pName is not defined");
    }

    public function setConstructor (array $pArgs = array())
    {
        $this->method('__construct', $pArgs);
        return $this;
    }
    public function hasConstructor ()
    {
        return $this->hasMethod('__construct');
    }
    public function getConstructor()
    {
        return $this->getMethod('__construct');
    }
    
    public function setShared ($pShared)
    {
        $this->_shared = $pShared;
    }
    
    public function isShared ()
    {
        return $this->_shared;
    }
    
    private function _assertMethodNameIsString ($pMethodName)
    {
        if (!is_string($pMethodName)) {
            throw new ContainerComponentDefinitionException("Method names can only be Strings");
        }
    }
    
    private function _assertPropertyNameIsString ($pPropertyName)
    {
        if (!is_string($pPropertyName)) {
            throw new ContainerComponentDefinitionException("Property names can only be Strings");
        }
    }

    private function _assertClassNameIsString ($pClassName)
    {
        if (!is_string($pClassName)) {
            throw new ContainerComponentDefinitionException("Class names can only be Strings");
        }
    }
}