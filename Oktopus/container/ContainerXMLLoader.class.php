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
            $this->_addComponentFromXmlNode($xmlComponentDefinition, $this->_container);
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
    
    private function _addComponentFromXmlNode (\SimpleXmlElement $pNode, $pContainer)
    {
        if (!isset ($pNode['id'])) {
            throw new ContainerComponentDefinitionException("Missing required attribute id for component definition in $pFilePath");
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
    }
}