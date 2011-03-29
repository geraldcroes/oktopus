<?php
namespace Oktopus;

class ContainerXMLLoader implements IContainer
{
    /**
     * The container
     * 
     * @var IMutableContainer
     */
    private $_container;

    public function __construct (IMutableContainer $pContainer)
    {
        $this->_container = $pContainer;
    }

    public function addXMLFile ($pFilePath)
    {
        if (!is_readable($pFilePath)) {
            throw new ContainerException("Cannot read XML file in [$pFilePath]");
        }

        $sXML = simplexml_load_file($pFilePath);
        foreach ($sXML->component as $xmlComponentDefinition) {
            $this->_addComponentFromXmlNode($xmlComponentDefinition, $this->_container, $pFilePath);
        }
    }

    public function get ($pId)
    {
        return $this->_container->get($pId);
    }

    public function hasComponent ($pId)
    {
        return $this->_container->hasComponent($pId);
    }    

    private function _addComponentFromXmlNode (\SimpleXmlElement $pNode, $pContainer, $pFilePath)
    {
        if (!isset ($pNode['id'])) {
            throw new ComponentDefinitionException("Missing required attribute id for component definition in $pFilePath");
        } elseif (isset($pNode['classname'])) {
            $id = (string) $pNode['id'];
            $className = (string) $pNode['classname'];
        } else {
            $id = $className = (string) $pNode['id'];
        }
        $component = $pContainer->define($id, $className);

        //Shared
        if (isset ($pNode['shared'])) {
            $component->setShared(filter_var((string) $pNode['shared'], FILTER_VALIDATE_BOOLEAN));
        }

        //Properties
        foreach ($pNode->property as $propertyDefinition) {
            if (! isset($propertyDefinition['name'])) {
                throw new ComponentDefinitionException("Missing required attribute name for property in component $id in $pFilePath");
            }
            $propertyName = (string) $propertyDefinition['name']; 

            if (isset($propertyDefinition['value'])) {
                $propertyValue = (string) $propertyDefinition['value'];
            } elseif (isset($propertyDefinition['component_reference'])) {
                $component_reference = (string) $propertyDefinition['component_reference'];
                $propertyValue = function() use($pContainer, $component_reference){
                    return $pContainer->get((string) $component_reference); 
                };
            } elseif (isset($propertyDefinition->value)) {
                if (!isset ($propertyDefinition->value->null)) {
                    $propertyValue = (string) $propertyDefinition->value;    
                } else {
                    $propertyValue = null;
                }
            } elseif (isset($propertyDefinition->component_reference)) {
                $component_reference = (string) $propertyDefinition->component_reference;
                $propertyValue = function() use($pContainer, $component_reference){
                    return $pContainer->get((string) $component_reference); 
                };
            } else {
                throw new ComponentDefinitionException("Missing required attribute or element for a value / component_reference in property $propertyName of component $id in file $pFilePath");
            }
            $component->setProperty($propertyName, $propertyValue);
        }
    }
}