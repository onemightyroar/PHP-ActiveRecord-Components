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
 * UpdatableModelTrait 
 *
 * Basic implementation of the UpdatableModelInterface
 *
 * @see UpdatableModelInterface
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
trait UpdatableModelTrait {

    /**
     * Safely update an entire model's properties
     *
     * @see UpdatableModelInterface
     * @param array $new_data
     * @param boolean $auto_save
     * @access public
     * @return boolean
     */
    public function update_profile( array $new_data, $auto_save = false ) {
        // Strip all of the data that isn't a model attribute
        $new_data = $this->filter_by_settable_attributes( $new_data );

        $this->set_attributes( $new_data );

        if ( $auto_save ) {
            return $this->save();
        }

        return true;
    }

} // End trait UpdatableModelTrait
