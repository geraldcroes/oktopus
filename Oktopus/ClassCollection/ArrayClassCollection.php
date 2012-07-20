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
    public function __construct(array $pCollection)
    {
        $this->classes = $pCollection;
    }

    /**
     * Gets the file path for the given file name
     *
     * @param $pClassName the class name we're looking for
     * @return string|null
     */
    public function getPath($pClassName)
    {
        if (array_key_exists($pClassName, $this->classes)) {
            return $this->classes[$pClassName];
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