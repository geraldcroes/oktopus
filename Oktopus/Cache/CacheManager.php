<?php
namespace Oktopus\Cache;

/**
 * Base interface for caching abilities
 *
 * @package Oktopus
 */
interface CacheManager
{
    public function get($key);

    public function set($key, $value);

    public function delete($key);

    public function exists($key);
}
