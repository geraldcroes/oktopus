<?php
namespace Oktopus\ClassCollection;

/**
 * A Class Collection that can be listed
 *
 * @package Oktopus
 */
interface KnownClassCollection extends ClassCollection
{
    public function getList();
}