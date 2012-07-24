<?php
namespace Oktopus\ClassCollection;

/**
 * Represents a class collection from an array
 *
 * @package Oktopus
 */
class ArrayClassCollection implements KnownClassCollection
{
    /**
     * @var array
     */
    protected $classes;

    /**
     * Constructor
     *
     * @param array $pCollection the class collection to initialize from
     */
    public function __construct(array $collection)
    {
        $this->classes = $collection;
    }

    /**
     * Gets the file path for the given file name
     *
     * @param $pClassName the class name we're looking for
     * @return string|null
     */
    public function getPath($className)
    {
        if (array_key_exists($className, $this->classes)) {
            return $this->classes[$className];
        }
        return null;
    }

    /**
     * Gets the list of known classes
     *
     * @return array
     */
    public function getList()
    {
        return $this->classes;
    }
}