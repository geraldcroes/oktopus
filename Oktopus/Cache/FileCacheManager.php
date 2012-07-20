<?php
namespace Oktopus\Cache;

/**
 * File caching
 * @package Oktopus
 */
class FileCacheManager implements CacheManager
{
    /**
     * Base path for cache files
     *
     * @var string
     */
    private $basePath;

    /**
     * @param string $basePath the base path where cache files will be written
     */
    public function __construct($basePath)
    {
        if (!is_string($basePath)) {
            throw new CacheException("Given base path is not a string");
        }
        $this->basePath = $basePath;
    }

    /**
     * Gets the cache value for key
     *
     * @param string $key the
     * @throws CacheException
     */
    public function get($key)
    {
        if ($this->exists($key)) {
            if (false === ($content = \file_get_contents($this->getFilePathForKey($key)))) {
                throw new CacheException("Could not read cache file for key $key");
            }
        } else {
            throw new CacheException("Element $key does not exists in cache");
        }
    }

    /**
     * Sets a value in the cache
     *
     * @param string $key
     * @param mixed  $value
     * @return FileCacheManager
     * @throws CacheException
     */
    public function set($key, $value)
    {
        if (false === \file_put_contents($this->getFilePathForKey($key), $value)) {
            throw new CacheException("Could not write cache data for $key");
        }
        return $this;
    }

    /**
     * Deletes a value from the cache
     *
     * @param $key
     * @return FileCacheManager
     */
    public function delete($key)
    {
        if ($this->exists($key)) {
            unlink($this->getFilePathForKey($key));
        }
        return $this;
    }

    /**
     * Says if a cache value is defined for a given key
     *
     * @param $key
     * @return int the timestamp of the filecache
     */
    public function exists($key)
    {
        $cacheFilePath = $this->getFilePathForKey($key);
        return filemtime($cacheFilePath);
    }

    /**
     * Gets the filepath for the given key in cache
     *
     * @param $key
     * @return string
     */
    protected function getFilePathForKey($key)
    {
        return $this->basePath . $key . '.php';
    }

    /**
     * Gets the base path of the cache in the file system
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }
}