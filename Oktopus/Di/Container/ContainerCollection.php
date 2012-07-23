<?php
namespace Oktopus\Di\Container;

use SplObjectStorage;

/**
 * A collection of containers
 */
class ContainerCollection implements Container
{
    /**
     * Containers attached to the collection
     *
     * @var SplOnjectStorage
     */
    private $containers;

    public function __construct()
    {
        $this->containers = new SplObjectStorage();
    }

    /**
     * @see Container::get
     * @param $pId
     * @return bool
     *
     * @throws ContainerException when trying to get an undefined component
     */
    public function get($pId)
    {
        foreach ($this->containers as $container) {
            if ($container->hasComponent($pId)) {
                return $container->get($pId);
            }
        }
        throw new ContainerException('Unknown component ' . $pId);
   }

    /**
     * @see Container::hasComponent
     * @param $pId
     * @return bool
     */
    public function hasComponent($pId)
    {
        foreach ($this->containers as $container) {
            if ($container->hasComponent($pId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds a container to the Collection
     *
     * @param Container $container
     * @return ContainerCollection
     */
    public function add(Container $container)
    {
        $this->containers->attach($container);
        return $this;
    }

    /**
     * Removes a container from the Collection.
     *
     * @param Container $container
     * @return ContainerCollection
     */
    public function remove(Container $container)
    {
        $this->containers->detach($container);
        return $this;
    }
}