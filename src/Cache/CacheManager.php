<?php

namespace LaravelViewAnalyzer\Cache;

class CacheManager
{
    protected AnalysisCache $cache;

    public function __construct(AnalysisCache $cache)
    {
        $this->cache = $cache;
    }

    public function generateKey(string $context): string
    {
        return md5($context.'_'.filemtime(base_path()));
    }

    public function clear(): void
    {
        $this->cache->flush();
    }
}
