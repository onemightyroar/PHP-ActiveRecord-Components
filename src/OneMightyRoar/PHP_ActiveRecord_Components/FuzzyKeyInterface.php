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
 * FuzzyKeyInterface
 *
 * Allow model records' keys to be accessed by a fuzzy method
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
interface FuzzyKeyInterface extends FuzzyFindInterface
{


    /**
     * Get the model's key from the reference
     *
     * This is conceptually the inverse of fuzzyFind
     *
     * @param mixed $reference A generic reference. If the type of the column
     *   matches the type of the key than the reference is directly returned.
     * @static
     * @access public
     * @return int
     */
    public static function fuzzyKey($reference);
}
