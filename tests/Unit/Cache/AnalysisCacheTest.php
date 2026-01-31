<?php

namespace LaravelViewAnalyzer\Tests\Unit\Cache;

use Illuminate\Support\Facades\Cache;
use LaravelViewAnalyzer\Cache\AnalysisCache;
use LaravelViewAnalyzer\Tests\TestCase;

class AnalysisCacheTest extends TestCase
{
    public function test_it_stores_and_retrieves_data()
    {
        Cache::shouldReceive('put')
            ->once()
            ->with('view_analyzer_test_key', 'test_value', 3600);

        Cache::shouldReceive('get')
            ->once()
            ->with('view_analyzer_test_key')
            ->andReturn('test_value');

        $cache = new AnalysisCache([
            'cache' => [
                'enabled' => true,
                'key_prefix' => 'view_analyzer_',
                'ttl' => 3600,
            ]
        ]);

        $cache->put('test_key', 'test_value');
        $this->assertEquals('test_value', $cache->get('test_key'));
    }

    public function test_it_does_not_store_or_retrieve_when_disabled()
    {
        Cache::shouldReceive('put')->never();
        Cache::shouldReceive('get')->never();

        $cache = new AnalysisCache([
            'cache' => [
                'enabled' => false,
            ]
        ]);

        $cache->put('test_key', 'test_value');
        $this->assertNull($cache->get('test_key'));
    }

    public function test_it_checks_existence()
    {
        Cache::shouldReceive('has')
            ->once()
            ->with('view_analyzer_test_key')
            ->andReturn(true);

        $cache = new AnalysisCache();
        $this->assertTrue($cache->has('test_key'));
    }

    public function test_it_forgets_item()
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('view_analyzer_test_key');

        $cache = new AnalysisCache();
        $cache->forget('test_key');
    }

    public function test_it_flushes_cache()
    {
        Cache::shouldReceive('flush')
            ->once();

        $cache = new AnalysisCache();
        $cache->flush();
    }
}
