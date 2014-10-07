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
     * Get strictly, or just return the instance itself
     *
     * @see FuzzyFindInterface::fuzzyFind()
     * @see AbstractModel::findStrict()
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
            return static::findStrict($reference);
        }
    }

    /**
     * Get the model's ID from the reference
     *
     * This is essentially the inverse of fuzzyFind
     *
     * @param mixed $reference A generic reference. If an integer is
     *   given it is assumed to be the model's ID.
     * @static
     * @access public
     * @return int
     */
    public static function fuzzyId($reference)
    {
        // If our is an int, just return it
        if (is_int($reference)) {
            return $reference;
        } else {
            // Try and get our model and its ID
            return static::fuzzyFind($reference)->id;
        }
    }
}
