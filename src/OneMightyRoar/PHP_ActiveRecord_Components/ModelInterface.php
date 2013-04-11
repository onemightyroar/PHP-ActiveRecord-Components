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
 * ModelInterface
 *
 * Generic model interface that
 * models SHOULD implement
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
interface ModelInterface {

    /**
     * Get a casted version of the object
     *
     * @access public
     * @return array
     */
    public function get_profile();

} // End interface ModelInterface
