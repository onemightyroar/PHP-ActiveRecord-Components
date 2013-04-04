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

use \LogicException;

/**
 * ReadOnlyAttributeException
 *
 * Exception thrown when writing to a read-only attribute.
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components\Exceptions
 */
class ReadOnlyAttributeException extends LogicException {
}
