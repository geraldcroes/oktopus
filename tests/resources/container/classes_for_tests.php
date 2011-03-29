<?php
class Fruit
{
}

class Apple extends Fruit
{
}

interface ITool
{
}

class Tool implements ITool
{
}

class Peeler extends Tool
{
}

class Juicer
{
    private $_fruit;
    private $_tool;
    
    public function getFruit ()
    {
        return $this->_fruit;
    }
    
    public function getTool ()
    {
        return $this->_tool;
    }
}

class EasyPrivate
{
    private $_property;
    
    public function getProperty ()
    {
        return $this->_property;
    }
}