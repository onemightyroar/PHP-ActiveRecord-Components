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
 * Utils 
 *
 * Static class for defining helper functions
 *
 * Keeping them in a class means that they're only
 * loaded when they NEED to be (lazy loaded via the autoloader)
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
class Utils
{

    // Check if a $haystack string starts with the string: $needle
    public static function startsWith($haystack, $needle)
    {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    // Check if a $haystack string ends with the string: $needle
    public static function endsWith($haystack, $needle)
    {
        return (substr($haystack, - strlen($needle)) === $needle);
    }

    // Convert a date/datetime to a MySQL timestamp string
    public static function dateToSqlTimestampString($datetime = null)
    {
        // Set SQL timestamp string format here
        $sql_timestamp_format = 'Y-m-d H:i:s';

        // Did we pass a string?
        if (is_string($datetime)) {
            // Convert to an int datetime
            $datetime = strtotime($datetime);
        } elseif (is_null($datetime)) {
            // Default to our current time
            $datetime = time();
        } elseif ($datetime instanceof ActiveRecord\DateTime) {
            // ActiveRecord DateTime objects already have a way of getting this format easily
            return $datetime->format('db');
        } elseif ($datetime instanceof DateTime) {
            // DateTime objects already have a way of getting this format easily
            return $datetime->format($sql_timestamp_format);
        }

        // Convert our int datetime to a string in the MySQL timestamp format
        return date($sql_timestamp_format, $datetime);
    }

    // Check if a string is in a valid SQL timestamp format
    public static function validateSqlTimestampString($sql_timestamp_string)
    {
        // Let's get a string version of the PHP timestamp that matches the correct SQL timestamp format
        $php_timestamp_string = self::dateToSqlTimestampString($sql_timestamp_string);

        // Return whether they're equal or not
        return ($sql_timestamp_string === $php_timestamp_string);
    }

    /**
     * Convert objects to an array of profiles
     *
     * @access protected
     * @param array $objects
     * @return array The objects, converted to profiles using the get_profile method in each model
     */
    public static function modelsToProfiles(array $objects = null, array $includes = array())
    {
        $return_array = array();

        if (is_null($objects)) {
            return $return_array;
        }

        foreach ($objects as $object) {
            if ($object instanceof ModelInterface === false) {
                throw new \InvalidArgumentException(
                    get_class($object) . ' should implement ' . __NAMESPACE__ . '\ModelInterface'
                );
            }

            $object_as_profile = $object->getProfile();

            // Add profiles for models we're including
            foreach ($includes as $include) {
                // Make sure the included object exists
                if (isset($object->{$include} ) && !is_null($object->{$include})) {
                    $object_to_include = $object->{$include};

                    if ($object_to_include instanceof ModelInterface === false) {
                        throw new \InvalidArgumentException(
                            get_class($object_to_include) . ' should implement ' . __NAMESPACE__ . '\ModelInterface'
                        );
                    }

                    $object_as_profile[$include] = $object_to_include->getProfile();
                }
            }

            $return_array[] = $object_as_profile;
        }

        return $return_array;
    }
}
