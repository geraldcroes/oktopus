<?php
namespace Oktopus\ClassCollection;

/**
 * Represents a Class Collection Classname / Filename
 *
 * @package Oktopus
 */
interface ClassCollection
{
    /**
     * @abstract
     * @param $pClassName the Class Name we're looking for
     * @return string|null String if founded, null if not
     */
    public function getPath($pClassName);
}