<?php
/**
 * PHP-ActiveRecord-Components
 *
 * Useful common components for a php-activerecord based project
 *
 * @copyright   2013 One Mighty Roar
 * @link        http://onemightyroar.com
 */

namespace OneMightyRoar\PHP_ActiveRecord_Components\Exceptions;

use \UnexpectedValueException;

use \ActiveRecord\Errors;

/**
 * ActiveRecordValidationException
 *
 * There was an error during active record validation
 *
 * @uses UnexpectedValueException
 * @package OneMightyRoar\PHP_ActiveRecord_Components\Exceptions
 */
class ActiveRecordValidationException extends UnexpectedValueException {

	/**
	 * Default exception message
	 *
	 * @var string
	 * @access protected
	 */
	protected $message = 'The posted data did not pass validation';

	/**
	 * ActiveRecord errors object
	 *
	 * @var \ActiveRecord\Errors
	 * @access protected
	 */
	protected $errors;


	/**
	 * Returns the exception's "errors" property/attribute
	 *
	 * @param boolean $as_array Whether to return the errors as an array or not
	 * @access public
	 * @return array
	 */
	public function getErrors( $as_array = false ) {
		if ( $as_array ) {
			if ( !is_null( $this->errors ) ) {
				$errors = $this->errors->get_raw_errors();
			}
			else {
				$errors = array();
			}
		}
		else {
			$errors = $this->errors;
		}

		return $errors;
	}

	/**
	 * Sets the exception's "errors" property/attribute
	 *
	 * @param \ActiveRecord\Errors $errors
	 * @param array $errors
	 * @access public
	 * @return array
	 */
	public function setErrors( Errors $errors ) {
		return $this->errors = $errors;
	}

	/**
	 * Alias of getErrors
	 *
	 * @see getErrors()
	 * @param boolean $as_array Whether to return the errors as an array or not
	 * @access public
	 * @return array
	 */
	public function get_errors( $as_array = false ) {
		return $this->getErrors( $as_array );
	}

	/**
	 * Alias of setErrors
	 *
	 * @see setErrors()
	 * @param \ActiveRecord\Errors $errors
	 * @access public
	 * @return array
	 */
	public function set_errors( Errors $errors ) {
		return $this->setErrors( $errors );
	}

} // End class ActiveRecordValidationException
