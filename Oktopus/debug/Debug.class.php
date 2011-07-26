<?php
/**
 * This file is a HEAVY port of Kohana_core, the license is under the kohana_license.
 * 
 * @copyright (c) 2011 Oktopus Team
 * @copyright (c) 2008-2010 Kohana Team
 * @author Gérald Croës <gerald@croes.org>
 * @package Oktopus
 */
namespace Oktopus;

/**
 * Class that gives services to format errors messages / data / ...
 *
 * @author Gérald Croës <gerald@croes.org>
 * @package Oktopus
 */
class Debug
{
    /**
     * Returns an HTML string, highlighting a specific line of a file, with some
     * number of lines padded above and below.
     *
     *     // Highlights the current line of the current file
     *     echo Oktopus\Debug::debug_source(__FILE__, __LINE__);
     *
     * @param string  file to open
     * @param integer line number to highlight
     * @param integer number of padding lines
     * 
     * @return  boolean|string   false if file is unreadable, or source of file
     */
    public static function debug_source($file, $lineNumber, $padding = 5)
    {
        if ( ! $file OR ! is_readable($file)) {
            // Continuing will cause errors
            return false;
        }

        // Open the file and set the line position
        $file = fopen($file, 'r');
        $line = 0;

        // Set the reading range
        $range = array('start' => $lineNumber - $padding, 'end' => $lineNumber + $padding);

        // Set the zero-padding amount for line numbers
        $format = '% '.strlen($range['end']).'d';

        $source = '';
        while (($row = fgets($file)) !== false) {
            // Increment the line number
            if (++$line > $range['end'])
            break;

            if ($line >= $range['start']) {
                // Make the row safe for output
                $row = htmlspecialchars($row, ENT_NOQUOTES, Engine::$charset);

                // Trim whitespace and sanitize the row
                $row = '<span class="number">'.sprintf($format, $line).'</span> '.$row;

                if ($line === $lineNumber) {
                    // Apply highlighting to this row
                    $row = '<span class="line highlight">'.$row.'</span>';
                } else {
                    $row = '<span class="line">'.$row.'</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }

        // Close the file
        fclose($file);

        return '<pre class="source"><code>'.$source.'</code></pre>';
    }

    /**
     * Returns an array of HTML strings that represent each step in the backtrace.
     *
     *     // Displays the entire current backtrace
     *     echo implode('<br/>', Oktopus\Debug::trace());
     *
     * @param array $trace the stacktrace
     * 
     * @return  string
     */
    public static function trace(array $trace = null)
    {
        if ($trace === null) {
            // Start a new trace
            $trace = debug_backtrace();
        }

        // Non-standard function calls
        $statements = array('include', 'include_once', 'require', 'require_once');

        $output = array();
        foreach ($trace as $step) {
            if ( ! isset($step['function'])) {
                // Invalid trace step
                continue;
            }

            if (isset($step['file']) AND isset($step['line'])) {
                // Include the source of this step
                $source = self::debug_source($step['file'], $step['line']);
            }

            if (isset($step['file'])) {
                $file = $step['file'];

                if (isset($step['line'])) {
                    $line = $step['line'];
                }
            }

            // function()
            $function = $step['function'];

            if (in_array($step['function'], $statements)) {
                if (empty($step['args'])) {
                    // No arguments
                    $args = array();
                } else {
                    // Sanitize the file path
                    $args = array($step['args'][0]);//Whould use simple_path to ease the reading
                }
            } elseif (isset($step['args'])) {
                if ( ! function_exists($step['function']) OR strpos($step['function'], '{closure}') !== false) {
                    // Introspection on closures or language constructs in a stack trace is impossible
                    $params = null;
                } else {
                    if (isset($step['class'])) {
                        if (method_exists($step['class'], $step['function'])) {
                            $reflection = new \ReflectionMethod($step['class'], $step['function']);
                        } else {
                            $reflection = new \ReflectionMethod($step['class'], '__call');
                        }
                    } else {
                        $reflection = new \ReflectionFunction($step['function']);
                    }

                    // Get the function parameters
                    $params = $reflection->getParameters();
                }

                $args = array();

                foreach ($step['args'] as $i => $arg) {
                    if (isset($params[$i])) {
                        // Assign the argument by the parameter name
                        $args[$params[$i]->name] = $arg;
                    } else {
                        // Assign the argument by number
                        $args[$i] = $arg;
                    }
                }
            }

            if (isset($step['class'])) {
                // Class->method() or Class::method()
                $function = $step['class'].$step['type'].$step['function'];
            }

            $output[] = array(
                'function' => $function,
                'args'     => isset($args)   ? $args : null,
                'file'     => isset($file)   ? $file : null,
                'line'     => isset($line)   ? $line : null,
                'source'   => isset($source) ? $source : null,
            );

            unset($function, $args, $file, $line, $source);
        }

        return $output;
    }

    /**
     * Returns an HTML string of information about a single variable.
     *
     * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
     *
     * @param mixed   $value  variable to dump
     * @param integer $length maximum length of strings
     * 
     * @return  string
     */
    public static function dump($value, $length = 128)
    {
        return Debug::_dump($value, $length);
    }

    /**
     * Helper for Oktopus\Debug::dump(), handles recursion in arrays and objects.
     *
     * @param mixed   $var     variable to dump
     * @param integer $length  maximum length of strings
     * @param integer $level   recursion level (internal)
     * 
     * @return  string
     */
    protected static function _dump( & $var, $length = 128, $level = 0)
    {
        if ($var === null) {
            return '<small>null</small>';
        } elseif (is_bool($var)) {
            return '<small>bool</small> '.($var ? 'true' : 'false');
        } elseif (is_float($var)) {
            return '<small>float</small> '.$var;
        } elseif (is_resource($var)) {
            if (($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var)) {
                $meta = stream_get_meta_data($var);

                if (isset($meta['uri'])) {
                    $file = $meta['uri'];

                    if (function_exists('stream_is_local')) {
                        // Only exists on PHP >= 5.2.4
                        if (stream_is_local($file)) {
                            $file = Debug::debug_path($file);
                        }
                    }

                    return '<small>resource</small><span>('.$type.
                        ')</span> '.htmlspecialchars($file, ENT_NOQUOTES, Engine::$charset);
                }
            } else {
                return '<small>resource</small><span>('.$type.')</span>';
            }
        } elseif (is_string($var)) {
            // Clean invalid multibyte characters. iconv is only invoked
            // if there are non ASCII characters in the string, so this
            // isn't too much of a hit.
            $var = UTF8::clean($var, Engine::$charset);

            if (UTF8::strlen($var) > $length) {
                // Encode the truncated string
                $str = htmlspecialchars(
                    UTF8::substr($var, 0, $length), 
                    ENT_NOQUOTES, Engine::$charset
                ).'&nbsp;&hellip;';
            } else {
                // Encode the string
                $str = htmlspecialchars($var, ENT_NOQUOTES, Engine::$charset);
            }

            return '<small>string</small><span>('.strlen($var).')</span> "'.$str.'"';
        } elseif (is_array($var)) {
            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            static $marker;

            if ($marker === null) {
                // Make a unique marker
                $marker = uniqid("\x00");
            }

            if (empty($var)) {
                // Do nothing
            } elseif (isset($var[$marker])) {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            } elseif ($level < 5) {
                $output[] = "<span>(";
                $var[$marker] = true;
                foreach ($var as $key => & $val) {
                    if ($key === $marker) continue;
                    if ( ! is_int($key)) {
                        $key = '"'.htmlspecialchars($key, ENT_NOQUOTES, Engine::$charset).'"';
                    }

                    $output[] = "$space$s$key => ".Debug::_dump($val, $length, $level + 1);
                }
                unset($var[$marker]);

                $output[] = "$space)</span>";
            } else {
                // Depth too great
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>array</small><span>('.count($var).')</span> '.implode("\n", $output);
        } elseif (is_object($var)) {
            // Copy the object as an array
            $array = (array) $var;

            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            $hash = spl_object_hash($var);

            // Objects that are being dumped
            static $objects = array();

            if (empty($var)) {
                // Do nothing
            } elseif (isset($objects[$hash])) {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            } elseif ($level < 10) {
                $output[] = "<code>{";

                $objects[$hash] = true;
                foreach ($array as $key => & $val) {
                    if ($key[0] === "\x00") {
                        // Determine if the access is protected or protected
                        $access = '<small>'.(($key[1] === '*') ? 'protected' : 'private').'</small>';

                        // Remove the access level from the variable name
                        $key = substr($key, strrpos($key, "\x00") + 1);
                    } else {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => ".Debug::_dump($val, $length, $level + 1);
                }
                unset($objects[$hash]);

                $output[] = "$space}</code>";
            } else {
                // Depth too great
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>'.get_class($var).'('.count($array).')</span> '.implode("\n", $output);
        } else {
            return '<small>'.gettype($var).'</small> '.
                   htmlspecialchars(print_r($var, true), ENT_NOQUOTES, Engine::$charset);
        }
    }

    /**
     * Inline exception handler, displays the error message, source of the
     * exception, and the stack trace of the error.
     *
     * @uses    Debug::exception_text
     * 
     * @param   Exception $e the exception that was not caugth
     * 
     * @return  boolean
     */
    public static function exceptionHandler(\Exception $e)
    {
        try {
            // Get the exception information
            $type    = get_class($e);
            $code    = $e->getCode();
            $message = $e->getMessage();
            $file    = $e->getFile();
            $line    = $e->getLine();

            // Create a text version of the exception
            $error = Debug::exception_text($e);

            // Get the exception backtrace
            $trace = $e->getTrace();

            if ($e instanceof ErrorException) {
                if (version_compare(PHP_VERSION, '5.3', '<')) {
                    // Workaround for a bug in ErrorException::getTrace() that exists in
                    // all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
                    for ($i = count($trace) - 1; $i > 0; --$i) {
                        if (isset($trace[$i - 1]['args'])) {
                            // Re-position the args
                            $trace[$i]['args'] = $trace[$i - 1]['args'];

                            // Remove the args
                            unset($trace[$i - 1]['args']);
                        }
                    }
                }
            }

            if ( ! headers_sent()) {
                // Make sure the proper content type is sent with a 500 status
                header('Content-Type: text/html; charset='.Engine::$charset, true, 500);
            }

            // Start an output buffer
            ob_start();
            // Include the exception HTML
            include __DIR__.'/error.view.php';
            // Display the contents of the output buffer
            echo ob_get_clean();

            return true;
        } catch (\Exception $e) {
            // Clean the output buffer if one exists
            ob_get_level() and ob_clean();

            // Display the exception text
            echo Debug::exception_text($e), "\n";

            // Exit with an error status
            exit(1);
        }
    }
    /**
     * Get a single line of text representing the exception:
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param Exception $e the Exception
     * 
     * @return  string
     */
    public static function exception_text(\Exception $e)
    {
        return sprintf(
            '%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e), $e->getCode(), 
            strip_tags($e->getMessage()), 
            Debug::debug_path($e->getFile()), 
            $e->getLine()
        );
    }

    /**
     * Removes application, system, modpath, or docroot from a filename,
     * replacing them with the plain text equivalents. Useful for debugging
     * when you want to display a shorter path.
     *
     *     // Displays SYSPATH/classes/Debug.php
     *     echo Debug::debug_path($path);
     *
     * @param string $file path to debug
     * 
     * @return string
     */
    public static function debug_path($file)
    {
        return $file;
    }

    /**
     * PHP error handler, converts all errors into ErrorExceptions. This handler
     * respects error_reporting settings.
     *
     * @throws  ErrorException
     * 
     * @return  true
     */
    public static function errorHandler($code, $error, $file = null, $line = null, $context = null)
    {
        if (! (error_reporting() & $code)) {
            return true;
        }
        switch ($code) {
            case E_ERROR:
            case E_USER_ERROR:
                $type = 'Error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $type = 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = 'Notice';
                break;
            case E_STRICT:
                $type = 'Strict';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $type = 'Deprecated';
                break;
            default:
                $type = 'Unknown Error';
        }
        $message = $error;
        if (count($trace = debug_backtrace()) > 2) {
            $trace = array_slice($trace, 2);
        } else {
            $trace = array();
        }

        // Start an output buffer
        ob_start();
        // Include the exception HTML
        include __DIR__.'/error.view.php';
        // Display the contents of the output buffer
        echo ob_get_clean();

    }

    /**
     * The old error handler
     * 
     * @var callback
     */
    private static $_oldErrorHandler;

    /**
     * The old exception handler
     * 
     * @var callback
     */
    private static $_oldExceptionHandler;

    /**
     * Sets the Oktopus\Debug::errorHandler as the error_handler
     */
    public static function registerErrorHandler ()
    {
        self::$_oldErrorHandler = set_error_handler('Oktopus\\Debug::errorHandler');
    }

    /**
     * Restores the old error_handler before registerErrorHandler was called
     */
    public static function unregisterErrorHandler ()
    {
        if (isset(self::$_oldErrorHandler)) {
            set_error_handler(self::$_oldErrorHandler);
        } else {
            restore_error_handler();
        }
    }

    /**
     * Sets the Oktopus\Debug::exceptionHandler as the exception_handler
     */
    public static function registerExceptionHandler ()
    {
        self::$_oldExceptionHandler = set_exception_handler('Oktopus\\Debug::exceptionHandler');
    }

    /**
     * Restores the old exception_handler before registerExceptionHandler was called
     */
    public static function unregisterExceptionHandler ()
    {
        if (isset(self::$_oldExceptionHandler)) {
            set_exception_handler(self::$_oldExceptionHandler);
        } else {
            restore_exception_handler();
        }
    }
}