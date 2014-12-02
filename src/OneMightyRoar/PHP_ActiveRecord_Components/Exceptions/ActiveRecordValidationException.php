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

use ActiveRecord\Errors;
use ActiveRecord\Model;
use Exception;
use UnexpectedValueException;

/**
 * ActiveRecordValidationException
 *
 * There was an error during active record validation
 *
 * @uses UnexpectedValueException
 * @package OneMightyRoar\PHP_ActiveRecord_Components\Exceptions
 */
class ActiveRecordValidationException extends UnexpectedValueException
{

    /**
     * Constants
     */

    /**
     * The default exception message
     *
     * @const string
     */
    const DEFAULT_MESSAGE = 'The posted data did not pass validation';


    /**
     * Properties
     */

    /**
     * Exception message
     *
     * @var string
     * @access protected
     */
    protected $message = self::DEFAULT_MESSAGE;

    /**
     * ActiveRecord errors object
     *
     * @var \ActiveRecord\Errors
     * @access protected
     */
    protected $errors;


    /**
     * Methods
     */

    /**
     * Create an instance using a validated model
     *
     * This static creation method is designed to allow for easier creation of
     * this exception while ridding of the common boilerplate used in the
     * majority of cases
     *
     * NOTE: This method is only useful if the passed model has gone through the
     * validation process.
     *
     * @param Model $model
     * @param string $message
     * @param int $code
     * @param Exception $previous
     * @static
     * @access public
     * @return ActiveRecordValidationException
     */
    public static function createFromValidatedModel(
        Model $model,
        $message = null,
        $code = null,
        Exception $previous = null
    ) {
        // Fall back to defaults
        $message = (null !== $message) ? $message : static::DEFAULT_MESSAGE;

        $exception = new static($message, $code, $previous);

        if (null !== $model->errors) {
            $exception->setErrors($model->errors);
        }

        return $exception;
    }

    /**
     * Returns the exception's "errors" property/attribute
     *
     * @param boolean $as_array Whether to return the errors as an array or not
     * @access public
     * @return array
     */
    public function getErrors($as_array = false)
    {
        if ($as_array) {
            if (!is_null($this->errors)) {
                $errors = $this->errors->get_raw_errors();
            } else {
                $errors = array();
            }
        } else {
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
    public function setErrors(Errors $errors)
    {
        return $this->errors = $errors;
    }

    /**
     * getErrors alias
     *
     * @deprecated Non PSR-2 compliant method name. Here for compatibility
     * @param boolean $as_array
     * @access public
     * @return array
     */
    public function get_errors($as_array = false)
    {
        return $this->getErrors($as_array);
    }

    /**
     * getErrors alias
     *
     * @deprecated Non PSR-2 compliant method name. Here for compatibility
     * @param Errors $errors
     * @access public
     * @return array
     */
    public function set_errors(Errors $errors)
    {
        return $this->setErrors($errors);
    }
}
