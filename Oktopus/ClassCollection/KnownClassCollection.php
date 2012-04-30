<?php
namespace Oktopus\ClassCollection;

/**
 * A Class Collection that can be listed
 */
interface KnownClassCollection extends ClassCollection
{
    public function getList ();
}