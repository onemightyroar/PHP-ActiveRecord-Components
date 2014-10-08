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

use ActiveRecord\Column;
use DateTime;

/**
 * FuzzyKeyTrait
 *
 * Basic, naive implementation of the FuzzyKeyInterface
 *
 * @see FuzzyKeyInterfaces
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
trait FuzzyKeyTrait
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
     * @return mixed
     */
    public static function fuzzyKey($reference)
    {
        $key_column = static::table()->columns[static::table()->pk[0]];

        // If our reference is the type of the primary key, return it.
        if (($key_column->type === Column::INTEGER && is_int($reference))
            || ($key_column->type === Column::STRING && is_string($reference))
            || ($key_column->type === Column::DECIMAL && is_numeric($reference))
            || (($key_column->type === Column::DATETIME
                || $key_column->type === Column::DATE
                || $key_column->type === Column::TIME)
                && $reference instanceof DateTime)) {

            return $reference;
        } else {
            // Try and get our model and its ID
            return static::fuzzyFind($reference)->getKey();
        }
    }
}
