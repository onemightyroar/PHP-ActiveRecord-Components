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

use \ActiveRecord\Model;

use \OneMightyRoar\PHP_ActiveRecord_Components\Exceptions\ActiveRecordValidationException;

/**
 * AbstractModel
 *
 * Base model which all models should extend
 *
 * @uses \ActiveRecord\Model
 * @uses ModelInterface
 * @abstract
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
abstract class AbstractModel extends Model implements ModelInterface {

    // Class properties
    static $default_conditions = array();

    /**
     * Blacklist of attributes that cannot be mass-assigned
     *
     * @see \ActiveRecord\Model::attr_protected
     * @static
     * @var array
     * @access public
     */
    static $attr_protected = array(
        'updated_at',
        'created_at',
    );


    /**
     * Constructor
     *
     * @see \ActiveRecord\Model::__construct()
     * @access public
     * @param array $attributes
     * @param boolean $guard_attributes
     * @param boolean $instantiating_via_find
     * @param boolean $new_record
     */
    public function __construct( array $attributes = array(), $guard_attributes = true, $instantiating_via_find = false, $new_record = true ) {
        $this->merge_static_attributes( 'attr_protected' );

        // Call our parent constructor AFTER we've merged our static attributes
        parent::__construct( $attributes, $guard_attributes, $instantiating_via_find, $new_record );
    }

    /**
     * Merge a static attribute by attribute name
     *
     * @param string $attribute_name
     * @final
     * @access private
     * @return boolean
     */
    final private function merge_static_attributes( $attribute_name ) {
        /**
         * These two attributes MAY be the same
         * (depends on how the child defines things, through late static binding)
         * (Get by reference)
         */
        $my_attribute =& self::${ $attribute_name };
        $child_attribute =& static::${ $attribute_name };

        // Did a child class actually set our attribute?
        if ( $child_attribute !== $my_attribute ) {
            return $child_attribute = array_merge(
                $my_attribute,
                $child_attribute
            );
        }

        return false;
    }

    /**
     * Get an array of all of the attribute names
     * 
     * @param boolean $only_settable Flag of whether or not to only return the attributes that are mass-assignable
     * @final
     * @access public
     * @return array
     */
    final public function get_attribute_names( $only_settable = true ) {
        if ( $only_settable ) {
            // If we've manually set which ones are accessible, just return that
            if ( count( static::$attr_accessible ) > 0 ) {
                return static::$attr_accessible;
            }
            else {
                return array_keys( array_diff_key(
                    $this->attributes(),
                    array_flip( static::$attr_protected )
                ));
            }
        }

        return array_keys( $this->attributes() );
    }

    /**
     * Filter a passed array of attributes by removing all of the keys that
     * either don't exist, aren't settable, or are protected from mass-assignment
     * 
     * @param array $attributes
     * @final
     * @access public
     * @return array
     */
    final public function filter_by_settable_attributes( array $attributes ) {
        return array_intersect_key(
            $attributes,
            array_flip( $this->get_attribute_names( true ) )
        );
    }

    /**
     * Save the model to the database
     *
     * Check for any validation errors and throw an exception if errors exist
     *
     * @see \ActiveRecord\Model::save()
     * @param boolean $validate Set to true or false depending on if you want the validators to run or not
     * @return boolean True if the model was saved to the database otherwise false
     */
    public function save( $validate = true ) {
        $success = parent::save( $validate );

        if( !$success ) {
            // Create a new exception and set our model validation error data
            $validation_exception = new ActiveRecordValidationException();
            $validation_exception->set_errors( $this->errors );

            throw $validation_exception;
        }

        return $success;
    }

    /**
     * Implement a basic version of our interface's method
     *
     * Simply returns an array of all of the attributes (key/value pairs)
     * that are normally accessible via mass-assignment
     *
     * @see ModelInterface::get_profile()
     * @access public
     * @return array
     */
    public function get_profile() {
        return $this->values_for(
            $this->get_attribute_names( true )
        );
    }

    /**
     * Get an attribute of the object
     *
     * @see \ActiveRecord\Model::__get()
     * @param string $name
     * @return mixed The value of the field
     */
    public function &__get( $name ) {
        // Check for getter
        if ( method_exists( $this, "get_$name" ) )
        {
            $name = "get_$name";
            $value = $this->$name();

            return $value;
        }
        // Is the name an "id"?
        elseif ( $name === "id" ) {
            $formatted_attribute = $this->format_integer_attribute( $name );

            return $formatted_attribute;
        }
        // Does the name end with "_at"?
        elseif ( Utils::ends_with( $name, '_at' ) ) {
            $formatted_attribute = $this->format_time_attribute( $name );

            return $formatted_attribute;
        }
        // Does the name start with "is_"?
        elseif ( Utils::starts_with( $name, 'is_' ) ) {
            $formatted_attribute = $this->format_boolean_attribute( $name );

            return $formatted_attribute;
        }

        // Otherwise, just use our parent's magic getter
        return parent::__get( $name );
    }

    /**
     * Function to format time object attributes
     *
     * @access protected
     * @param string $name
     * @return string The formatted time as a string
     */
    protected function format_time_attribute( $name ) {
        return (string) $this->read_attribute( $name );
    }

    /**
     * Function to format boolean attributes
     *
     * @access protected
     * @param string $name
     * @return boolean The value of the boolean field
     */
    protected function format_boolean_attribute( $name ) {
        return (bool) $this->read_attribute( $name );
    }

    /**
     * Function to format integer attributes
     *
     * @access protected
     * @param string $name
     * @return integer The value of the integer field
     */
    protected function format_integer_attribute( $name ) {
        return (int) $this->read_attribute( $name );
    }

} // End class AbstractModel
