<?php
namespace Oktopus\Parser;

/**
 * Interface for class parsing
 *
 * @package Oktopus\Parser
 */
interface ClassParser
{
    /**
     * Algorithm to find classes in a given file
     *
     * @param string $pFileName the filename to inspect
     */
    public function find($pFileName);
}