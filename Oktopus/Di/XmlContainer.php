<?php
namespace Oktopus\Di;

/**
 * @package Oktopus
 */
class XmlContainer implements Container
{
    /**
     * The container
     */
    private $container;

    public function __construct($filePath)
    {
        $this->container = new BasicContainer();
        $this->loadFile($filePath);
    }

    public function get($pId)
    {
        return $this->container->get($pId);
    }

    public function hasComponent($pId)
    {
        return $this->container->hasComponent($pId);
    }

    private function loadFile($filePath)
    {
        if (!is_readable($filePath)) {
            throw new ContainerException("Cannot read XML file in [$filePath]");
        }

        $sXml = simplexml_load_file($filePath);
        foreach ($sXml->component as $xmlComponentDefinition) {
            $this->addComponentFromXmlNode($xmlComponentDefinition, $this->container, $filePath);
        }
    }

    private function addComponentFromXmlNode(\SimpleXmlElement $pNode, $pContainer, $pFilePath)
    {
        if (!isset ($pNode['id'])) {
            throw new ComponentDefinitionException("Missing required attribute id for component definition in $pFilePath");
        } elseif (isset($pNode['classname'])) {
            $id = (string)$pNode['id'];
            $className = (string)$pNode['classname'];
        } else {
            $id = $className = (string)$pNode['id'];
        }
        $component = $pContainer->define($id, $className);

        //Shared
        if (isset ($pNode['shared'])) {
            $component->setShared(filter_var((string)$pNode['shared'], FILTER_VALIDATE_BOOLEAN));
        }

        //Properties
        foreach ($pNode->property as $propertyDefinition) {
            if (!isset($propertyDefinition['name'])) {
                throw new ComponentDefinitionException("Missing required attribute name for property in component $id in $pFilePath");
            }
            $propertyName = (string)$propertyDefinition['name'];
            $propertyValue = $this->getValueFromNode($propertyDefinition, $pContainer, $id, $pFilePath);
            $component->setProperty($propertyName, $propertyValue);
        }

        //methods
        foreach ($pNode->method as $methodDefinition) {
            if (!isset($methodDefinition['name'])) {
                throw new ComponentDefinitionException("Missing required attribute name for method in component $id in $pFilePath");
            }
            $methodName = (string)$methodDefinition['name'];
            $arguments = array();
            if (isset($methodDefinition->argument)) {
                foreach ($methodDefinition->argument as $methodArgumentDefinition) {
                    $arguments[] = $this->getValueFromNode($methodArgumentDefinition, $pContainer, $id, $pFilePath);
                }
            }
            $component->setMethod($methodName, $arguments);
        }

        //Constructor
        $arguments = array();
        foreach ($pNode->constructor_argument as $constructorArgumentDefinition) {
            $arguments[] = $this->getValueFromNode($constructorArgumentDefinition, $pContainer, $id, $pFilePath);
        }
        if (count($arguments)) {
            $component->setConstructorArguments($arguments);
        }

        //Factory method
        $arguments = array();
        if (isset($pNode->factory)) {
            if (!isset ($pNode->factory['classname'])) {
                throw new ComponentDefinitionException("Missing required attribute classname for factory in component $id in $pFilePath");
            } else {
                $factoryName = (string)$pNode->factory['classname'];
            }

            if (!isset ($pNode->factory['method'])) {
                throw new ComponentDefinitionException("Missing required attribute method for factory in component $id in $pFilePath");
            } else {
                $factoryMethodName = (string)$pNode->factory['method'];
            }

            $arguments = array();
            foreach ($pNode->factory->argument as $argumentDefinition) {
                $arguments[] = $this->getValueFromNode($argumentDefinition, $pContainer, $id, $pFilePath);
            }
            $component->setFactory(array($factoryName, $factoryMethodName), $arguments);
        }
    }

    private function getValueFromNode(\SimpleXmlElement $pPropertyDefinition, Container $pContainer, $pId, $pFilePath)
    {
        $propertyValue = null;

        if (isset($pPropertyDefinition['value'])) {
            $propertyValue = (string)$pPropertyDefinition['value'];
        } elseif (isset($pPropertyDefinition['component_reference'])) {
            $component_reference = (string)$pPropertyDefinition['component_reference'];
            $propertyValue = function() use($pContainer, $component_reference)
            {
                return $pContainer->get((string)$component_reference);
            };
        } elseif (isset($pPropertyDefinition->value)) {
            if (!isset($pPropertyDefinition->value->null)) {
                $propertyValue = (string)$pPropertyDefinition->value;
            } else {
                $propertyValue = null;
            }
        } elseif (isset($pPropertyDefinition->component_reference)) {
            $component_reference = (string)$pPropertyDefinition->component_reference;
            $propertyValue = function() use($pContainer, $component_reference)
            {
                return $pContainer->get((string)$component_reference);
            };
        } else {
            throw new ComponentDefinitionException("Missing required attribute or element for a value / component_reference in property {$pPropertyDefinition['name']} of component $pId in file $pFilePath");
        }
        return $propertyValue;
    }
}
