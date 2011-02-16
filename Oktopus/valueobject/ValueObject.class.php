<?php
namespace Oktopus;
/**
 * @package Oktopus
 * @author "Gérald Croës <gerald@croes.org>"
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */


/**
 * Value Object
 *
 * @package Oktopus
 */
class ValueObject implements \ArrayAccess {
    /**
     * Gets the property by its name
     *
     * @param mixed $pName
     * @return mixed
     */
    public function &__get ($pName) {
        return $this->$pName;
    }

    public function __isset ($pName) {
        if ($this->$pName instanceof ValueObject) {
            $test = get_object_vars($this->$pName);
            return !empty($test);
        }
        return isset($this->$pName);
    }

    /**
     * Construct
     *
     * @param array $pArInit
     */
    public function __construct ($pArInit = array ()) {
        if (is_string($pArInit)) {
            $exploded = explode(';', $pArInit);
            $array = array();

            foreach ($exploded as $item) {
                $item = explode ('=>', $item);
                if (count ($item) == 2){
                    $array[$item[0]] = $item[1];
                }else{
                    $array[] = $item[0];
                }
            }
            $pArInit = $array;
        }

        if (is_array ($pArInit)) {
            foreach ($pArInit as $key => $item) {
                $this->$key = $item;
            }
        } elseif (is_object ($pArInit)) {
            foreach (get_object_vars ($pArInit) as $key => $item) {
                $this->$key = $item;
            }
        }
    }

    public function offsetGet ($pOffset) {
        return $this->$pOffset;
    }

    public function offsetSet ($pOffset, $pValue) {
        if ($pOffset === null) {
            $vars = get_object_vars($this);
            if (count($vars) === 0) {
                $pOffset = 0;
            } else {
                $pOffset = max(array_keys(get_object_vars ($this))) + 1;
            }
        }
        $this->$pOffset = $pValue;
    }

    public function offsetExists ($pOffset) {
        return isset($this->$pOffset);
    }

    public function offsetUnset ($pOffset) {
        $this->$pOffset = null;
    }

    public function merge ($pToMerge) {
        if (is_array ($pToMerge)) {
            $pToMerge = new ValueObject($pToMerge);
        } else if (is_object ($pToMerge)) {
            if (!($pToMerge instanceof ValueObject)) {
                $pToMerge = new ValueObject(get_object_vars($pToMerge));
            }
        } else {
            $pToMerge = array ($pToMerge);
        }

        foreach (get_object_vars ($pToMerge) as $name => $prop) {
            if (!isset ($this->$name)){
                $this->$name = $prop;
            }
        }
        return $this;
    }

    public function loadFrom ($pData, $pCreateNew = true){
        $valueObject = new ValueObject($pData);
        $valueObject->saveIn($this, $pCreateNew);
        return $this;
    }

    public function saveIn (&$pDest, $pCreateNew = true) {
        //détermine le "type" de l'objet
        if (!($array = is_array ($pDest))) {
            if (!($object = is_object ($pDest))) {
                $natural = true;
            }
        }
        $elementVars = array ();
        if ($array || $object) {
            $elementVars = array_keys ($this->_getElementVars ($pDest));
        }

        //on parcours chacune des propriétés de l'élément
        foreach (get_object_vars ($this) as $name => $element) {
            //on regarde si la propriété existe dans la destination
            if (($inArray = in_array ($name, $elementVars)) || $pCreateNew) {
                if ($inArray && (is_object ($element) || is_array ($element))) {
                    //la propriété existait déja et c'est un tableau ou un objet,
                    //on reparcours le tout pour y appliquer les changements
                    if ($array) {
                        $valueObject = new ValueObject($element);
                        $valueObject->saveIn ($pDest[$name], $pCreateNew);
                    } else {
                        $valueObject = new ValueObject($element);
                        $valueObject->saveIn ($pDest->$name, $pCreateNew);
                    }
                    // NOTE : il n'est pas possible d'avoir recours a l'opérateur
                    // ternaire pour les passages par référence
                } else {
                    // la propriété n'existait pas => il faut la créer a l'identique
                    // ou la propriété existait sous sa forme naturelle et il faut la remplacer
                    if ($array) {
                        $pDest[$name] = $element;
                    } else {
                        $pDest->$name = $element;
                    }
                }
            }
        }

        return $this;
    }

    protected function _getElementVars ($pElement) {
        return (is_array ($pElement)) ? $pElement : get_object_vars ($pElement);
    }
}

/**
 * RecursiveValueObject
 *

 * @package Oktopus
 */
class RecursiveValueObject extends ValueObject {
	public function & __get ($pName) {
		$this->$pName = new RecursiveValueObject ();
		return $this->$pName;
	}
}
