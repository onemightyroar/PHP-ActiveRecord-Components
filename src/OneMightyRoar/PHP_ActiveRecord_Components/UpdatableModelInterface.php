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
 * UpdatableModelInterface 
 *
 * Interface for models that are updateable
 * via mass-assignment of properties
 *
 * @uses ModelInterface
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
interface UpdatableModelInterface extends ModelInterface {

    /**
     * Safely update an entire model's properties
     *
     * @param array $new_data
     * @param boolean $auto_save Flag to automatically try and save the model after updating
     * @access public
     * @return array
     */
    public function update_profile( array $new_data, $auto_save = false );

} // End interface UpdatableModelInterface
