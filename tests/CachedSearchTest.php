<?php

namespace {
    if (!class_exists('Events', false)) {
        class Events
        {
            public static array $dispatched = [];

            public static function dispatch(string $name, $event): void
            {
                self::$dispatched[] = [$name, $event];
            }
        }
    }
}

namespace ClassKit\Tests {

    use Stash\Pool;
    use Stash\Driver\Ephemeral;
    use PHPUnit\Framework\TestCase;
    use ClassKit\Search\CachedSearch;
    use Concrete\Core\Support\Facade\Facade;
    use Concrete\Core\Application\Application;
    use Concrete\Core\Cache\Level\ExpensiveCache;

    class CachedSearchTestLogger
    {
        public array $messages = [];

        public function getLogger(): self
        {
            return $this;
        }

        public function addDebug(string $message): void
        {
            $this->messages[] = $message;
        }
    }

    class CachedSearchTestList
    {
        public static int $runs = 0;
        public array $keywords = [];

        public function filterByKeywords(string $keywords): void
        {
            $this->keywords[] = $keywords;
        }

        public function getResultIDs(): array
        {
            ++self::$runs;

            return $this->keywords ? [1, 2] : [1, 2, 3];
        }
    }

    class CachedSearchTest extends TestCase
    {
        private Pool $pool;

        public function testConstructorAcceptsLoggerClassName(): void
        {
            $search = new CachedSearch(CachedSearchTestLogger::class, 'search_results', 60);

            $this->assertInstanceOf(CachedSearch::class, $search);
        }

        public function testSearchWorksWithoutQueryBuilder(): void
        {
            $search = new CachedSearch(CachedSearchTestLogger::class);

            $this->assertSame([1, 2, 3], $search->search(CachedSearchTestList::class, '/search'));
        }

        public function testSearchAppliesQueryBuilderAndServesRepeatFromCache(): void
        {
            $search = new CachedSearch(CachedSearchTestLogger::class);
            $builder = fn($list) => $list->filterByKeywords('news');

            $this->assertSame([1, 2], $search->search(CachedSearchTestList::class, '/search', ['q' => 'a/b'], $builder));
            $this->assertSame([1, 2], $search->search(CachedSearchTestList::class, '/search', ['q' => 'a/b'], $builder));
            $this->assertSame(1, CachedSearchTestList::$runs);
        }

        public function testCacheKeysAreHashedAndEventReceivesReadableQuery(): void
        {
            $search = new CachedSearch(CachedSearchTestLogger::class);
            $search->search(CachedSearchTestList::class, '/search/news', ['q' => 'a/b']);

            $keys = $search->getAllCachedKeys();
            $this->assertCount(1, $keys);
            $this->assertMatchesRegularExpression('/^search_[0-9a-f]{32}$/', $keys[0]);

            [$name, $event] = \Events::$dispatched[0];
            $this->assertSame('on_search_block_query', $name);
            $this->assertSame('/search/news?q=a%2Fb', $event->getArgument('query'));
            $this->assertSame(3, $event->getArgument('resultCount'));
        }

        public function testClearAllRemovesCachedSearches(): void
        {
            $search = new CachedSearch(CachedSearchTestLogger::class);
            $search->search(CachedSearchTestList::class, '/search', ['a' => 1]);
            $search->search(CachedSearchTestList::class, '/search', ['a' => 2]);
            $this->assertCount(2, $search->getAllCachedKeys());

            $search->clearAll();

            $this->assertSame([], $search->getAllCachedKeys());
            $search->search(CachedSearchTestList::class, '/search', ['a' => 1]);
            $this->assertSame(3, CachedSearchTestList::$runs);
        }

        protected function setUp(): void
        {
            $this->pool = new Pool(new Ephemeral());
            $pool = $this->pool;

            $app = new Application();
            $app->instance(ExpensiveCache::class, new class($pool) {
                public function __construct(private Pool $pool) {}

                public function getPool(): Pool
                {
                    return $this->pool;
                }
            });
            $app->instance('helper/url', new class {
                public function buildQuery(string $url, array $params): string
                {
                    return $params ? $url . '?' . http_build_query($params) : $url;
                }
            });
            Facade::setFacadeApplication($app);

            CachedSearchTestList::$runs = 0;
            \Events::$dispatched = [];
        }
    }
}
