<?php
namespace Oktopus;

/**
 * The component reference.
 * 
 * @author geraldc
 */
class ComponentReference
{
    /**
     * The ID of the component that is refered to 
     * 
     * @var String
     */ 
    private $_id;
    
    /**
     * The container where the id will be looked for
     * 
     * @var IContainer
     */
    private $_container;

    /**
     * Construction
     * 
     * @param string $pId the refered component id
     * @param IContainer $pContainer the container
     */
    public function __construct ($pId, IContainer $pContainer = null)
    {
        $this->_id = $pId;
        $this->_container = $pContainer;
    }
    
    /**
     * Gets the id 
     * 
     * @return string
     */
    public function getId ()
    {
        return $this->_id;
    }
    
    /**
     * Returns the container
     * 
     * @return IContainer
     */
    public function getContainer ()
    {
        return $this->_container;
    }
}