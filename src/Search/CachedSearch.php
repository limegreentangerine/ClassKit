<?php

namespace ClassKit\Search;

use Events;
use Psr\Cache\CacheItemPoolInterface;
use Concrete\Core\Application\Application;

/**
 * Class CachedSearch.
 */
class CachedSearch
{
    protected Application $app;
    protected CacheItemPoolInterface $cache;
    protected int $ttl;
    protected object $logger;
    protected string $indexKey;

    /**
     * Executes __construct.
     */
    public function __construct(object $loggerClass, string $indexKey = 'search_results', int $ttl = 3600)
    {
        $this->app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $this->cache = $this->app->make(\Concrete\Core\Cache\Level\ExpensiveCache::class)->getPool();
        $this->indexKey = $indexKey;
        $this->ttl = $ttl;
        $this->logger = $this->app->make($loggerClass)->getLogger();
    }

    /**
     * Executes storeCacheKey.
     */
    protected function storeCacheKey(string $cacheKey): void
    {
        $indexItem = $this->cache->getItem($this->indexKey);
        $index = $indexItem->isHit() ? $indexItem->get() : [];

        if (!in_array($cacheKey, $index, true)) {
            $index[] = $cacheKey;
            $indexItem->set($index)->expiresAfter($this->ttl);
            $this->cache->save($indexItem);
            $this->logger->addDebug(sprintf('Added search cache key %s with an expiry of %s', $cacheKey, $this->ttl));
        }
    }

    /**
     * Executes getCacheKey.
     */
    protected function getCacheKey(string $searchUrl, array $filters): string
    {
        $uh = $this->app->make('helper/url');
        return $uh->buildQuery($searchUrl, $filters);
    }

    /**
     * Executes saveQuery.
     */
    protected function saveQuery(string $query = '', ?int $results = 0)
    {
        $ev = new \Symfony\Component\EventDispatcher\GenericEvent('on_search_block_query', [
            'query' => $query,
            'resultCount' => $results,
        ]);
        Events::dispatch('on_search_block_query', $ev);
    }

    /**
     * Executes search.
     */
    public function search(object $searchClass, string $searchUrl = '', array $filters = [], ?callable $queryBuilder = null)
    {
        $cacheKey = $this->getCacheKey($searchUrl, $filters);

        // Try cache
        $cachedItem = $this->cache->getItem($cacheKey);
        if ($cachedItem->isHit()) {
            $this->saveQuery($cacheKey, count($cachedItem->get()));
            return $cachedItem->get();
        }

        // Build and run query
        $pl = $this->app->make($searchClass);
        $queryBuilder($pl); // apply filters from controller
        $ids = $pl->getResultIDs(); // only fetch IDs

        // Save to cache
        $cachedItem->set($ids)->expiresAfter($this->ttl);
        $this->cache->save($cachedItem);

        // Save key to index
        $this->storeCacheKey($cacheKey);
        $this->saveQuery($cacheKey, count($ids));

        return $ids;
    }

    /**
     * Executes getAllCachedKeys.
     */
    public function getAllCachedKeys(): array
    {
        $indexItem = $this->cache->getItem($this->indexKey);
        $keys = $indexItem->isHit() ? $indexItem->get() : [];

        // Filter out keys that are no longer valid
        $validKeys = [];
        foreach ($keys as $key) {
            $item = $this->cache->getItem($key);
            if ($item->isHit()) {
                $validKeys[] = $key;
            }
        }

        // Update index to remove stale keys
        $indexItem->set($validKeys)->expiresAfter($this->ttl);
        $this->cache->save($indexItem);

        return $validKeys;
    }

    /**
     * Executes clearAll.
     */
    public function clearAll(): void
    {
        $indexItem = $this->cache->getItem($this->indexKey);
        $allKeys = $indexItem->isHit() ? $indexItem->get() : [];

        // Delete each key, whether it exists in the cache or not
        foreach ($allKeys as $key) {
            $this->cache->deleteItem($key);
        }

        // Delete the index itself
        $this->cache->deleteItem($this->indexKey);

        $this->logger->addDebug('Cleared all cached searches for index key: ' . $this->indexKey);
    }
}
