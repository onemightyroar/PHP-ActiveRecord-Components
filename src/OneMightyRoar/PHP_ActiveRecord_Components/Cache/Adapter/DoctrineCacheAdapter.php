<?php
/**
 * PHP-ActiveRecord-Components
 *
 * Useful common components for a php-activerecord based project
 *
 * @copyright   2013 One Mighty Roar
 * @link        http://onemightyroar.com
 */

namespace OneMightyRoar\PHP_ActiveRecord_Components\Cache\Adapter;

use Doctrine\Common\Cache\CacheProvider;
use OneMightyRoar\PHP_ActiveRecord_Components\Cache\CacheAdapterInterface;

/**
 * DoctrineCacheAdapter
 *
 * An adapter to translate a Doctrine {@see CacheProvider} into a PHP-ActiveRecord
 * compatible cache adapter, through the {@see CacheAdapterInterface}
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components\Cache\Adapter
 */
class DoctrineCacheAdapter implements CacheAdapterInterface
{

    /**
     * Constants
     */

    /**
     * The default value for the option of whether or not to flush the namespace
     * instead of the entire cache for flush operations
     *
     * @const bool
     */
    const FLUSH_NAMESPACE_DEFAULT = false;


    /**
     * Properties
     */

    /**
     * The Doctrine cache provider to be adapted
     *
     * @var CacheProvider
     * @access private
     */
    private $adapted_provider;

    /**
     * Whether or not to flush the namespace instead of the entire cache when
     * committing a cache flush
     *
     * @var bool
     * @access protected
     */
    protected $flush_namespace = self::FLUSH_NAMESPACE_DEFAULT;


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * @param CacheProvider $cache_provider The Doctrine cache provider to be adapted
     * @param bool $flush_namespace Whether or not to flush the namespace (vs full) when commiting a cache flush
     */
    public function __construct(CacheProvider $cache_provider, $flush_namespace = self::FLUSH_NAMESPACE_DEFAULT)
    {
        $this->adapted_provider = $cache_provider;
        $this->flush_namespace = (bool) $flush_namespace;
    }

    /**
     * Get the underlying adapted Doctrine cache provider
     *
     * @access public
     * @return CacheProvider
     */
    public function getAdaptedCache()
    {
        return $this->adapted_provider;
    }

    /**
     * Get the value of flush_namespace
     *
     * @access public
     * @return bool
     */
    public function getFlushNamespace()
    {
        return $this->flush_namespace;
    }

    /**
     * Read a value from the cache at the specified key
     *
     * @param string $key The unique cache key used to locate/identify the contents to read
     * @access public
     * @return mixed The cached value
     */
    public function read($key)
    {
        return $this->adapted_provider->fetch($key);
    }

    /**
     * Write a value to the cache at the specified key with an optional TTL
     *
     * @param string $key The unique cache key used to designate/identify the stored location
     * @param mixed $value The value to cache
     * @param int $ttl An optional time-to-live in seconds, for future-expiring cache data
     * @access public
     * @return void
     */
    public function write($key, $value, $ttl = null)
    {
        $this->adapted_provider->save($key, $value, $ttl);
    }

    /**
     * Flush all cache entries
     *
     * @access public
     * @return void
     */
    public function flush()
    {
        if ($this->flush_namespace) {
            $this->adapted_provider->deleteAll();
        } else {
            $this->adapted_provider->flushAll();
        }
    }
}
