<?php
namespace Oktopus;

/**
 * @author geraldcroes
 */
class LazyBuilder {
	/**
	 * Current known Builders 
	 * 
	 * @var unknown_type
	 */
	private static $_builders;

	public static function addBuilder ($pKey, $pLazyBuilder){
		if (array_key_exists ($pKey, self::$_builders)) {
			throw new LazyBuilderException ($pKey, LazyBuilderException::KEY_EXISTS);
		}else{
			if (! class_exists ($pLazyBuilder, true )) {
				throw new LazyBuilderException ($pLazyBuilder, LazyBuilderException::UNKNOWN_BUILDER);
			}

			if (! in_array ('ILazyBuilder', class_implements ($pLazyBuilder))) {
				throw new LazyBuilderException ($callbackClassname, LazyBuilderException::NOT_A_VALID_BUILDER);
			}
			self::$_builders[$pKey] = $callbackClassname;
		}
	}

	public static function builder ($pName){
		if (array_key_exists ($pName, self::$_builders)){
			return self::$_builders[$pName];
		}
	}
}


class Object {
	public function __construct (){
		if (CopixConfig::instance ()->getConfig ('cache')){
			//...		
		}else{
			//...
		}
	}
}


class ObjectBuilder implements ILazyBuilder {
	public function configure (Object $object){
		$object->setCache (true);
		$object->setDatabaseConnection ();
	}	
}






























interface ILazyBuilder {
	public function configure ($pObject, $pParameters = null);
}

class LazyBuilderException {
	const KEY_EXISTS = 1;
	const UNKNOWN_BUILDER = 2;
	const NOT_A_VALID_BUILDER = 3;
}

class Object {
	public function __construct (){
		LazyBuilder::builder ('object')->configure ($this);
		if ($this->cache ()){
		}
	}
}

class ObjectBuilder implements ILazyBuilder {
	public function configure (Object $object){
		$object->setCacheEnabled (true);
	}
}
LazyBuilder::addBuilder('object', 'ObjectBuilder');

class ObjectFactory {
	public function create (){
		$object = new Object ();
		return LazyBuilder::builder ('object')->configure ($object);
	}
}


LazyBuilder::createObject ($varName);
return self::builder ($varName)->configure (new $varName ());

class Buildable {
	public function __construct (){
		LazyBuilder::builder (__CLASS__)->configure ($this);
	}
}