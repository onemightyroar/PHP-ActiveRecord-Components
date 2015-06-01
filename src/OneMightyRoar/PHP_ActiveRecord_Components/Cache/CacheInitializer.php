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

use ActiveRecord\Cache;

/**
 * CacheInitializer
 *
 * Eases the setup of the PHP-ActiveRecord cache through a psuedo-factory,
 * allowing more type-safe initialization and compatibility
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components\Cache
 */
class CacheInitializer
{

    /**
     * Constants
     */

    /**
     * The option map key for the "namespace" option
     *
     * @type string
     */
    const OPTION_KEY_NAMESPACE = 'namespace';

    /**
     * The option map key for the "expire" option
     *
     * @type string
     */
    const OPTION_KEY_EXPIRE = 'expire';


    /**
     * Methods
     */

    /**
     * Statically initializes the global Cache adapter
     *
     * While the static global state is far from ideal, this provides pure compatibility
     * with AR's use of the cache implementation without a deep refactor or BC break
     *
     * @param CacheAdapterInterface $adapter The adapter to use for the cache
     * @param string $namespace An optional namespace to prefix all cache keys with
     * @param int $default_ttl The default expiry/TTL, in seconds, for each cache entry
     * @return void
     */
    public static function init(CacheAdapterInterface $adapter, $namespace = null, $default_ttl = null)
    {
        Cache::$adapter = $adapter;

        Cache::$options = [
            static::OPTION_KEY_NAMESPACE => (string) $namespace,
            static::OPTION_KEY_EXPIRE => (int) $default_ttl,
        ];
    }
}
