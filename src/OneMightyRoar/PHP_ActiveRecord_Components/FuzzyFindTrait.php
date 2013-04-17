<?php
/**
 * PHP-ActiveRecord-Components
 *
 * Useful common components for a php-activerecord based project
 *
 * @copyright   2013 One Mighty Roar
 * @link        http://onemightyroar.com
 */

namespace OneMightyRoar\PHP_ActiveRecord_Components;

/**
 * FuzzyFindTrait
 *
 * Basic, naive implementation of the FuzzyFindInterface
 *
 * @see FuzzyFindInterface
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
trait FuzzyFindTrait
{

    /**
     * Get by ID, or just return the instance itself
     *
     * @see FuzzyFindInterface::fuzzyFind()
     * @param mixed $reference
     * @static
     * @access public
     * @return static
     */
    public static function fuzzyFind($reference)
    {
        // If our reference is an actual instance of "us"
        if ($reference instanceof static) {
            return $reference;
        } else {
            // Try and get our model by ID
            return static::find($reference);
        }
    }
}
