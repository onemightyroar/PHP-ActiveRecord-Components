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
 * FuzzyFindInterface
 *
 * Allow model records to be accessed by a fuzzy-finding method
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
interface FuzzyFindInterface
{

    /**
     * Allow for an easy method call for any other method to take a
     * generic reference (ID, SLUG, or an actual instance) of a model
     * and return the actual model instance, intelligently
     *
     * @param mixed $reference
     * @static
     * @access public
     * @return Model
     */
    public static function fuzzyFind($reference);
}
