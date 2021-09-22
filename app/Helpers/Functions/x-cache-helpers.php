<?php

use Illuminate\Support\Facades\Cache;

//get cache
function x_cache_store()
{
    return Cache::store('file');
}

//cache has key
function x_cache_has(string $key)
{
    return x_cache_store()->has($key);
}

//cache set
function x_cache_set(string $key, $data, $seconds=null)
{
    $cache = x_cache_store();
    $seconds = is_numeric($seconds) ? (int) $seconds : null;
    if (is_integer($seconds)) {
        return $cache->put($key, $data, $seconds);
    }

    return $cache->put($key, $data); //true
}

//cache get
function x_cache_get(string $key, $default=null)
{
    $cache = x_cache_store();

    return $cache->has($key) ? $cache->get($key) : $default;
}

//cache pull (get & delete)
function x_cache_pull(string $key, $default=null)
{
    $cache = x_cache_store();

    return $cache->has($key) ? $cache->pull($key) : $default;
}

//cache delete
function x_cache_delete(string $key)
{
    $cache = x_cache_store();
    if ($cache->has($key)) {
        $cache->forget($key);
    }
}

//cache flush (delete all)
function x_cache_flush()
{
    return x_cache_store()->flush();
}
