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

class ConstructedOneParameter
{
	private $_first;
	private $_moreNoSetter;
	
	private $_moreSetter;
	private $_moreSetter2;
	
	public function __construct ($pFirst)
	{
		$this->_first = $pFirst;
	}
	
	public function getFirst ()
	{
		return $this->_first;
	}
	
	public function setMore ($pMore)
	{
		$this->_moreSetter = $pMore;
	}
	
	public function getMore ()
	{
		return $this->_moreSetter;
	}

	public function setMore2 ($pMore)
	{
		$this->_moreSetter2 = $pMore;
	}
	public function getMore2 ()
	{
		return $this->_moreSetter2;
	}
}

class ConstructedTwoParameter extends ConstructedOneParameter
{
	private $_second;
	
	public function __construct ($pFirst, $pSecond)
	{
		$this->_second = $pSecond;
	}
	
	public function getSecond ()
	{
		return $this->_second;
	}
	
	public function setMoreValues ($pMore, $pMore2)
	{
		$this->setMore($pMore);
		$this->setMore2($pMore2);
	}
}

class FactoryConstructedNoParameter
{
	public static function create ()
	{
		return new ConstructedTwoParameter('one', 'two');
	}
}

class FactoryConstructedOneParameter
{
	public static function create ($pOne)
	{
		return new ConstructedTwoParameter($pOne, 'two2');
	}
}

class FactoryConstructedTwoParameter
{
	public static function create ($pOne, $pTwo)
	{
		return new ConstructedTwoParameter($pOne, $pTwo);
	}
	
}