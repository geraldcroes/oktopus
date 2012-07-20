<?php
namespace Oktopus\Di\Container;

/**
 * Interface for containers where you can add / update definitions
 * @package Oktopus
 */
interface MutableContainer extends Container
{
    public function define($pId, $pClassName = null);

    public function getDefinition($pId);
}