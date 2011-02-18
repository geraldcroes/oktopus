<?php
/**
 * Value Object
 * 
 * @package Oktopus
 * @author  "Gérald Croës <gerald@croes.org>"
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Oktopus;

/**
 * Value Object
 *
 * @package Oktopus
 * @author  "Gérald Croës<gerald@croes.org>"
 */
class ValueObject implements \ArrayAccess
{
    /**
     * The properties of the value object
     * 
     * @var array
     */
    private $_properties = array();

    /**
     * Test the existance of a given property.
     * 
     * An empty value object is considered not set
     *  
     * @param string $pName the property to test
     * 
     * @return boolean
     */
    public function __isset ($pName)
    {
        if (array_key_exists($pName, $this->_properties)) {
            return isset($this->_properties[$pName]);
        }
        return false;
    }

    /**
     * Construct
     *
     * @param array $pArInit initial data
     */
    public function __construct ($pArInit = array ())
    {
        if (is_string($pArInit)) {
            $exploded = explode(';', $pArInit);
            $array = array();

            foreach ($exploded as $item) {
                $item = explode('=>', $item);
                if (count($item) == 2) {
                    $array[$item[0]] = $item[1];
                } else {
                    $array[] = $item[0];
                }
            }
            $pArInit = $array;
        }

        if (is_array($pArInit)) {
            foreach ($pArInit as $key => $item) {
                $this->$key = $item;
            }
        } elseif (is_object($pArInit)) {
            if ($pArInit instanceof ValueObject) {
                foreach ($pArInit->asArray() as $key => $item) {
                    $this->$key = $item;
                }
            } else {
                foreach (get_object_vars($pArInit) as $key => $item) {
                    $this->$key = $item;
                }
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet ($pOffset)
    {
        return $this->$pOffset;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet ($pOffset, $pValue)
    {
        if ($pOffset === null) {
            if (count($this->_properties) === 0) {
                $pOffset = 0;
            } else {
                $pOffset = max(array_keys($this->_properties)) + 1;
            }
        }
        $this->$pOffset = $pValue === null ? new ValueObject() : $pValue;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists ($pOffset)
    {
        return $this->__isset($pOffset);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset ($pOffset)
    {
        $this->$pOffset = null;
    }

    /**
     * Will merge its data with the given source
     *  
     * The merge function won't override existing properties.
     * The merged data will stay as is (not converted to ValueObject)
     * 
     * @param mixed $pToMerge data to merge with the current object
     * 
     * @return Oktopus\ValueObject
     */
    public function mergeWith ($pToMerge)
    {
        if (is_object($pToMerge)) {
            if ($pToMerge instanceof ValueObject) {
                $pToMerge = $pToMerge->asArray();
            } else {
                $pToMerge = get_object_vars($pToMerge);
            }
        } elseif (!is_array($pToMerge)) {
            $pToMerge = array($pToMerge);
        }

        foreach ($pToMerge as $name => $prop) {
            if (!isset($this->$name)) {
                $this->$name = $prop;
            }
        }
        return $this;
    }

    /**
     * Loads data from a string / array / object
     * 
     * @param mixed   $pData      the source of data
     * @param boolean $pCreateNew if we want to import non existing properties
     * 
     * @return Oktoups\ValueObject $this
     */
    public function loadFrom ($pData, $pCreateNew = true)
    {
        $valueObject = new ValueObject($pData);
        $valueObject->saveIn($this, $pCreateNew);
        return $this;
    }

    /**
     * Saves the object properties in a given destination
     * 
     * If the destination has a property with the same name, it will be 
     *   - Overwritten if it's a primitive (string, int, boolean, ...)
     *   - Merged as an array if destination is an array
     *   - Merged as an object if destination is an object
     *   
     * During a merge, if the properties exists both in the source and the destination, 
     *   the destination value will be lost
     * 
     * @param mixed   $pDest      the variable that will be written
     * @param boolean $pCreateNew if we want to create properties that do not exist 
     *                            in the destination (default is true)
     *                            
     * @return Oktopus\ValueObject the original object 
     */
    public function saveIn (&$pDest, $pCreateNew = true)
    {
        if (!($array = is_array($pDest))) {
            $object = is_object($pDest);
        } else {
            $object = false;
        }
        if ($array || $object) {
            $elementVars = array_keys($this->_getElementVars($pDest));
        } else {
            $pDest = $this->_properties;
            return $this;
        }

        foreach ($this->_properties as $name => $element) {
            if (($inArray = in_array($name, $elementVars)) || $pCreateNew) {
                if ($inArray) {
                    if (is_array($element) || is_object($element)) {
                        if (($array && is_array($pDest[$name])) || ($object && is_object($pDest->$name))) {
                            $valueObject = new ValueObject($element);
                            if ($array) {
                                $valueObject->saveIn($pDest[$name], $pCreateNew);
                            } else {
                                $valueObject->saveIn($pDest->$name, $pCreateNew);
                            }
                        } else {
                            if ($array) {
                                $pDest[$name] = $element;
                            } else {
                                $pDest->$name = $element;
                            }
                        }
                    } else {
                        if ($array) {
                            $pDest[$name] = $element;
                        } else {
                            $pDest->$name = $element;
                        }
                    }
                } else {
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

    /**
     * Gets the elements variables (properties if an object, asArray if a valueObject, $elements if not)
     * 
     * @param object|array $pElement the element we wants to get the properties
     * 
     * @return array
     */
    private function _getElementVars ($pElement)
    {
        if (is_array($pElement)) {
            return $pElement;
        } elseif ($pElement instanceof ValueObject) {
            return $pElement->asArray();
        } else {
            return get_object_vars($pElement);
        }
    }

    /**
     * Gets a property by its name
     * 
     * If the property does not exists yet, will return a new empty property
     * 
     * @param string $pName the property name we want to get
     * 
     * @return mixed
     */
    public function & __get ($pName)
    {
        if (! array_key_exists($pName, $this->_properties)) {
            $this->_properties[$pName] = new ValueObject();
        }
        return $this->_properties[$pName];
    }

    /**
     * Magic method to set a property
     * 
     * If we set "null" to the property, an empty ValueObject is written instead
     *  
     * @param string $pName  the name of the property to set
     * @param mixed  $pValue the value of the property
     * 
     * @return void
     */
    public function __set ($pName, $pValue)
    {
        if ($pValue === null) {
            unset ($this->_properties[$pName]);
        } else {
            $this->_properties[$pName] = $pValue;
        }
    }

    /**
     * Gets the value object as an array (for the first dimension only) 
     * 
     * @return array
     */
    public function asArray ()
    {
        return $this->_properties;
    }
}