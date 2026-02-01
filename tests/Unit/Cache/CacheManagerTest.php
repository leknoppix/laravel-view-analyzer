<?php

namespace LaravelViewAnalyzer\Tests\Unit\Cache;

use LaravelViewAnalyzer\Cache\AnalysisCache;
use LaravelViewAnalyzer\Cache\CacheManager;
use LaravelViewAnalyzer\Tests\TestCase;
use Mockery;

class CacheManagerTest extends TestCase
{
    public function test_it_generates_cache_key()
    {
        $cache = Mockery::mock(AnalysisCache::class);
        $manager = new CacheManager($cache);

        $key = $manager->generateKey('test-context');

        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key)); // MD5 length
    }

    public function test_it_clears_cache()
    {
        $cache = Mockery::mock(AnalysisCache::class);
        $cache->shouldReceive('flush')->once();

        $manager = new CacheManager($cache);
        $manager->clear();

        $this->assertTrue(true);
    }
}
