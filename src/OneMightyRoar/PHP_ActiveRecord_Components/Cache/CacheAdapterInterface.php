<?php
/**
 * PHP-ActiveRecord-Components
 *
 * Useful common components for a php-activerecord based project
 *
 * @copyright   2013 One Mighty Roar
 * @link        http://onemightyroar.com
 */

namespace OneMightyRoar\PHP_ActiveRecord_Components\Cache;

/**
 * CacheAdapterInterface
 *
 * An interface for the {@link \ActiveRecord\Cache} adapter
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components\Cache
 */
interface CacheAdapterInterface
{

    /**
     * Read a value from the cache at the specified key
     *
     * @param string $key The unique cache key used to locate/identify the contents to read
     * @access public
     * @return mixed The cached value
     */
    public function read($key);

    /**
     * Write a value to the cache at the specified key with an optional TTL
     *
     * @param string $key The unique cache key used to designate/identify the stored location
     * @param mixed $value The value to cache
     * @param int $ttl An optional time-to-live in seconds, for future-expiring cache data
     * @access public
     * @return void
     */
    public function write($key, $value, $ttl = null);

    /**
     * Flush all cache entries
     *
     * @access public
     * @return void
     */
    public function flush();
}
