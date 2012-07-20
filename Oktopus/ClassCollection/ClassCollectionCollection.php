<?php
namespace Oktopus\ClassCollection;

class ClassCollectionCollection implements ClassCollection
{
    private $collections;

    public function __construct ()
    {
        $this->collection = new \SplObjectStorage();
    }

    public function add (ClassCollection $collection)
    {
        $this->collection->attach($collection);
        return $this;
    }

    public function remove ()
    {
        $this->collection->detach($collection);
        return $this;
    }

    public function getList()
    {
        $list = array();
        foreach ($this->collection as $collection) {
            $list += $collection->getList();
        } 
        return $list;
    }
    
    public function getPath ($className)
    {
        foreach ($this->collection as $collection) {
            if (($path = $collection->getPath($className)) !== null) {
                return $path;
            }
        }
        return null;
    }
}
