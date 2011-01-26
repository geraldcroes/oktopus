<?php
namespace Oktopus;

/**
 * Base Oktopus Exception
 *
 * @author geraldcroes
 */
class Exception extends \Exception {}

/**
 * Exception when objects are called in an invalid state
 *
 * @author geraldcroes
 */
class ObjectNotReadyException extends Exception {}

/**
 * Exception when wrong parameters are given to method / functions
 *
 * @author geraldcroes
 */
class WrongParameterException extends Exception {}