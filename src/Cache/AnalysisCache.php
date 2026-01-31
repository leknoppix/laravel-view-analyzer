<?php

namespace LaravelViewAnalyzer\Cache;

use Illuminate\Support\Facades\Cache;

class AnalysisCache
{
    protected string $keyPrefix;

    protected int $ttl;

    protected bool $enabled;

    public function __construct(array $config = [])
    {
        $this->keyPrefix = $config['cache']['key_prefix'] ?? 'view_analyzer_';
        $this->ttl = $config['cache']['ttl'] ?? 3600;
        $this->enabled = $config['cache']['enabled'] ?? true;
    }

    public function get(string $key): mixed
    {
        if (! $this->enabled) {
            return null;
        }

        return Cache::get($this->keyPrefix . $key);
    }

    public function put(string $key, mixed $value): void
    {
        if (! $this->enabled) {
            return;
        }

        Cache::put($this->keyPrefix . $key, $value, $this->ttl);
    }

    public function has(string $key): bool
    {
        if (! $this->enabled) {
            return false;
        }

        return Cache::has($this->keyPrefix . $key);
    }

    public function forget(string $key): void
    {
        Cache::forget($this->keyPrefix . $key);
    }

    public function flush(): void
    {
        Cache::flush();
    }
}
