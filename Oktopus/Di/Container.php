<?php
namespace Oktopus\Di;

/**
 * Interface for Oktopus Containers
 * @package Oktopus
 */
interface Container
{
    public function get ($pId);
    public function hasComponent($pId);
}