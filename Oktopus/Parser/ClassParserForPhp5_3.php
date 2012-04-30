<?php
namespace Oktopus\Parser;

/**
 * Algorithm to find classes in a given file
 *
 * <code>
 * <?php
 *    $Parser = new CodeParserForPhp5_3();
 *    $classes = $Parser->find('/path/to/file/name.php');
 *    // $classes is now an array with every classes and interfaces declared
 *    // in name.php
 * ?>
 * </code>
 *
 * @see ClassParser
 *
 * @package Oktopus\Parser
 */
class ClassParserForPhp5_3 implements ClassParser
{
    /**
     * Find classes in $pFileName
     *
     * @param string $pFileName the filename to inspect
     *
     * @return array
     */
    public function find ($pFileName)
    {
        $toReturn = array();
        $tokens = token_get_all(file_get_contents($pFileName, false));

        $currentNamespace = '';
        $namespaceHunt = false;
        $validatedNamespaceHunt = false;
        $classHunt = false;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_INTERFACE || $token[0] === T_CLASS) {
                    $classHunt = true;
                    continue;
                } elseif ($token[0] === T_NAMESPACE) {
                    $namespaceHunt = true;
                    continue;
                }
                if ($classHunt && $token[0] === T_STRING) {
                    $toReturn[] = (strlen($currentNamespace) > 0 ? $currentNamespace.'\\' : '').$token[1];
                    $classHunt = false;
                } elseif ($namespaceHunt && $validatedNamespaceHunt
                          && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
                    $currentNamespace .= $token[1];
                } elseif ($namespaceHunt && !$validatedNamespaceHunt && $token[0] === T_WHITESPACE) {
                    $currentNamespace = '';
                    $validatedNamespaceHunt = true;
                } elseif ($namespaceHunt && !$validatedNamespaceHunt && $token[0] !== T_WHITESPACE) {
                    $namespaceHunt = false;
                }
            } else {
                if ($token === ';' || $token === '{') {
                    //ends the "default" namespace only
                    if ($namespaceHunt && !$validatedNamespaceHunt && $token === '{') {
                        $currentNamespace = '';
                    }
                    $classHunt = false;
                    $namespaceHunt = false;
                    $validatedNamespaceHunt = false;
                }
            }
        }
        return $toReturn;
    }
}