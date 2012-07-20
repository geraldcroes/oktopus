<?php
namespace Oktopus\Di;

use Oktopus\Di\Container\Container;

/**
 * The component reference.
 *
 * @author geraldc
 * @package Oktopus
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
     * @var Container
     */
    private $_container;

    /**
     * Construction
     *
     * @param string $pId the refered component id
     * @param Container $pContainer the container
     */
    public function __construct($pId, Container $pContainer = null)
    {
        $this->_id = $pId;
        $this->_container = $pContainer;
    }

    /**
     * Gets the id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->_container;
    }
}