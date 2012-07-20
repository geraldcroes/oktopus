<?php
namespace Oktopus\ClassCollection;

/**
 * Adds a caching ability to ClassCollections
 */
class ClassCollectionCacheDecorator implements ClassCollection
{
    /**
     * Proxied ClassCollection
     *
     * @var \Oktopus\ClassCollection
     */
    private $classCollection;

    /**
     * Cache manager
     *
     * @var \Oktopus\CacheManager
     */
    private $cacheManager;

    /**
     * @param ClassCollection $classCollection the collection to be cached
     * @param CacheManager $cacheManager the cache manager
     */
    public function __construct(ClassCollection $classCollection, CacheManager $cacheManager)
    {
        $this->classCollection = $classCollection;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Gets the file path of a given class
     * @param string $className
     */
    public function getPath($className)
    {
        //If the path is in cache, get it from cache
        if ($this->cacheManager->exists($className)) {
            return $this->cacheManager->get($className);
        }

        //if the path is not in cache, put it in cache
        $path = $this->classCollection->getPath($className);
        $this->cacheManager->set($className, $path);
        return $path;
    }
}