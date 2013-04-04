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
class Utils {

	// Check if a $haystack string starts with the string: $needle
	public static function starts_with( $haystack, $needle ) {
		return ( substr( $haystack, 0, strlen( $needle ) ) === $needle );
	}

	// Check if a $haystack string ends with the string: $needle
	public static function ends_with( $haystack, $needle ) {
		return ( substr( $haystack, - strlen( $needle ) ) === $needle );
	}

	// Convert a date/datetime to a MySQL timestamp string
	public static function date_to_sql_timestamp_string( $datetime = null ) {
		// Set SQL timestamp string format here
		$sql_timestamp_format = 'Y-m-d H:i:s';

		// Did we pass a string?
		if ( is_string( $datetime ) ) {
			// Convert to an int datetime
			$datetime = strtotime( $datetime );
		}
		elseif ( is_null( $datetime ) ) {
			// Default to our current time
			$datetime = time();
		}
		// ActiveRecord DateTime objects already have a way of getting this format easily
		elseif ( $datetime instanceof ActiveRecord\DateTime ) {
			return $datetime->format( 'db' );
		}
		// DateTime objects already have a way of getting this format easily
		elseif ( $datetime instanceof DateTime ) {
			return $datetime->format( $sql_timestamp_format );
		}

		// Convert our int datetime to a string in the MySQL timestamp format
		return date( $sql_timestamp_format, $datetime );
	}

	// Check if a string is in a valid SQL timestamp format
	public static function validate_sql_timestamp_string( $sql_timestamp_string ) {
		// Let's get a string version of the PHP timestamp that matches the correct SQL timestamp format
		$php_timestamp_string = self::date_to_sql_timestamp_string( $sql_timestamp_string );

		// Return whether they're equal or not
		return ( $sql_timestamp_string === $php_timestamp_string );
	}

} // End class Utils
