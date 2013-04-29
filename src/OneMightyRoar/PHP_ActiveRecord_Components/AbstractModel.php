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
use \ActiveRecord\Inflector;
use \ActiveRecord\RecordNotFound;

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
abstract class AbstractModel extends Model implements ModelInterface
{

    /**
     * Class constants
     */

    /**
     * The default limit of returned rows in a query
     *
     * @const int
     */
    const DEFAULT_LIMIT = 10;

    /**
     * The default row offset of multiple result queries
     *
     * @const int
     */
    const DEFAULT_OFFSET = 0;

    /**
     * The default column to order by
     *
     * @const string
     */
    const DEFAULT_ORDER_COL = 'id';

    /**
     * The default direction to order by
     *
     * DESC = descending
     * ASC  = ascending
     *
     * @const string
     */
    const DEFAULT_ORDER_DIR = 'DESC';


    /**
     * Class properties
     */

    /**
     * Blacklist of attributes that cannot be mass-assigned
     *
     * @see \ActiveRecord\Model::attr_protected
     * @static
     * @var array
     * @access public
     */
    public static $attr_protected = array(
        'updated_at',
        'created_at',
    );

    /**
     * Default attribute values
     *
     * @static
     * @var array
     * @access protected
     */
    protected static $default_values = array();


    /**
     * Methods
     */

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
    public function __construct(
        array $attributes = array(),
        $guard_attributes = true,
        $instantiating_via_find = false,
        $new_record = true
    ) {
        $this->mergeStaticAttributes('attr_protected');

        // Call our parent constructor AFTER we've merged our static attributes
        parent::__construct($attributes, $guard_attributes, $instantiating_via_find, $new_record);
    }

    /**
     * Merge a static attribute by attribute name
     *
     * @param string $attribute_name
     * @final
     * @access private
     * @return boolean
     */
    final private function mergeStaticAttributes($attribute_name)
    {
        /**
         * These two attributes MAY be the same
         * (depends on how the child defines things, through late static binding)
         * (Get by reference)
         */
        $my_attribute =& self::${$attribute_name};
        $child_attribute =& static::${$attribute_name};

        // Did a child class actually set our attribute?
        if ($child_attribute !== $my_attribute) {
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
    final public function getAttributeNames($only_settable = true)
    {
        if ($only_settable) {
            // If we've manually set which ones are accessible, just return that
            if (count(static::$attr_accessible) > 0) {
                return static::$attr_accessible;
            } else {
                return array_keys(
                    array_diff_key(
                        $this->attributes(),
                        array_flip(static::$attr_protected)
                    )
                );
            }
        }

        return array_keys($this->attributes());
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
    final public function filterBySettableAttributes(array $attributes)
    {
        return array_intersect_key(
            $attributes,
            array_flip($this->getAttributeNames(true))
        );
    }

    /**
     * Assert that a result was valid/non-empty
     *
     * @param mixed $result
     * @static
     * @final
     * @access public
     * @throws \ActiveRecord\RecordNotFound If the result is empty/invalid
     * @return boolean
     */
    final public static function assertResult($result)
    {
        if (empty($result)) {
            throw new RecordNotFound();
        }

        return true;
    }

    /**
     * Strict finder
     *
     * This finder simply runs a normal "find()" method, but
     * throws a "RecordNotFound" exception if the result is empty
     *
     * NOTE: This method takes a variable number of args.
     * See {@link \ActiveRecord\Model::find()} for more info
     *
     * @see \ActiveRecord\Model::find()
     * @static
     * @access public
     * @throws \ActiveRecord\RecordNotFound If the result is empty/invalid
     * @return mixed
     */
    public static function findStrict()
    {
        $result = call_user_func_array('static::find', func_get_args());

        // Asser that we actually got a result
        self::assertResult($result);

        return $result;
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
    public function save($validate = true)
    {
        $success = parent::save($validate);

        if (!$success) {
            // Create a new exception and set our model validation error data
            $validation_exception = new ActiveRecordValidationException();
            $validation_exception->setErrors($this->errors);

            throw $validation_exception;
        }

        return $success;
    }

    /**
     * Get the default values of the attributes, if there are any
     * 
     * @static
     * @access public
     * @return array
     */
    public static function getDefaultValues()
    {
        return static::$default_values;
    }

    /**
     * Get the default value for a specific attribute, if there is any
     *
     * @param string $attribute_name The name of the attribute
     * @static
     * @access public
     * @return mixed|null
     */
    public static function getDefaultValueFor($attribute_name)
    {
        if (isset(static::$default_values[$attribute_name])) {
            return static::$default_values[$attribute_name];
        }

        return null;
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
    public function getProfile()
    {
        return $this->values_for(
            $this->getAttributeNames(true)
        );
    }

    /**
     * Build our paging options based on a passed array of raw
     * options and/or possible aliases
     *
     * Any alias options will be overwritten if their respective ActiveRecord equivalents
     * are passed directly
     *
     * @param array $paging_options The various paging options
     *   @named int $per_page The amount of results to return per call, per page
     *   @named string $order_col The column to order by
     *   @named boolean $order_desc Whether to order in the descending direction
     * @param int $page The page number to request
     * @static
     * @access public
     * @return array An ActiveRecord compatible array holding query options related to paging
     */
    public static function buildPagingOptions(array $paging_options, $page = null)
    {
        // Get our master options (the raw ActiveRecord options, not aliases)
        $order      = isset($paging_options['order'])   ? $paging_options['order']  : null;
        $limit      = isset($paging_options['limit'])   ? $paging_options['limit']  : null;
        $offset     = isset($paging_options['offset'])  ? $paging_options['offset'] : null;

        $per_page   = isset($paging_options['per_page'])   ? (int) $paging_options['per_page']    : self::DEFAULT_LIMIT;

        if (isset($paging_options['order_col'])) {
            $order_col = $paging_options['order_col'];
        } elseif (isset($paging_options['order_by'])) {
            $order_col = $paging_options['order_by'];
        } else {
            $order_col = self::DEFAULT_ORDER_COL;
        }

        if (isset($paging_options['order_desc'])) {
            $order_desc = $paging_options['order_desc'];
        } elseif (isset($paging_options['order_descending'])) {
            $order_desc = $paging_options['order_descending'];
        } else {
            $order_desc = null;
        }

        if (is_null($page)) {
            $page   = isset($paging_options['page'])       ? (int) $paging_options['page']        : 1;
        }

        // Define our default order option
        if (is_null($order)) {
            if (is_null($order_desc)) {
                $order_dir = self::DEFAULT_ORDER_DIR;
            } else {
                $order_desc = strcasecmp($order_desc, 'false') !== 0;
                $order_dir = $order_desc ? 'DESC' : 'ASC';
            }

            $order = $order_col . ' ' . $order_dir;
        }

        if (is_null($limit)) {
            $limit = $per_page;
        }

        if (is_null($offset)) {
            if ($page <= 0) {
                $page = 1;
            }

            $offset = (($page - 1) * $per_page) + self::DEFAULT_OFFSET;
        }

        // Define and return our query options array
        return array(
            'order'  => $order,
            'limit'  => $limit,
            'offset' => $offset,
        );
    }

    /**
     * Determine if the multiple result query has a next "page"
     *
     * This simply checks if there's at least one more row for
     * an offset based on the past limit and offset
     *
     * @param array $orig_query_opts
     * @static
     * @access public
     * @return boolean
     */
    public static function hasNextPage(array $orig_query_opts)
    {
        $original_limit  = isset($orig_query_opts['limit'])  ? $orig_query_opts['limit']  : self::DEFAULT_LIMIT;
        $original_offset = isset($orig_query_opts['offset']) ? $orig_query_opts['offset'] : self::DEFAULT_OFFSET;

        // Modify our original options
        $options = array_merge(
            $orig_query_opts,
            array(
                'limit' => 1, // We only care if there's at least one more result
                'offset' => ($original_limit + $original_offset),
            )
        );

        $number_of_results = count(static::all($options));

        return ($number_of_results > 0);
    }

    /**
     * Get an attribute of the object
     *
     * @see \ActiveRecord\Model::__get()
     * @param string $name
     * @return mixed The value of the field
     */
    public function &__get($name)
    {
        // Camelize the name
        $camelized_name = Inflector::instance()->camelize($name);

        // Check for getter
        if (method_exists($this, "get_$name")) {
            $name = "get_$name";
            $value = $this->$name();

            return $value;
        } elseif (method_exists($this, "get$camelized_name")) {
            $name = "get$camelized_name";
            $value = $this->$name();

            return $value;
        } elseif ($name === "id") { // Is the name an "id"?
            $formatted_attribute = $this->formatIntegerAttribute($name);

            return $formatted_attribute;
        } elseif (Utils::endsWith($name, '_at')) { // Does the name end with "_at"?
            $formatted_attribute = $this->formatTimeAttribute($name);

            return $formatted_attribute;
        } elseif (Utils::startsWith($name, 'is_')) { // Does the name start with "is_"?
            $formatted_attribute = $this->formatBooleanAttribute($name);

            return $formatted_attribute;
        }

        // Otherwise, just use our parent's magic getter
        return parent::__get($name);
    }

    /**
     * Set an attribute of the object
     *
     * @see \ActiveRecord\Model::__set()
     * @param string $name
     * @param mixed $value The value of the field
     * @return mixed
     */
    public function __set($name, $value)
    {
        // Camelize the name
        $camelized_name = Inflector::instance()->camelize($name);

        // Check for setter
        if (method_exists($this, "set_$name")) {
            $name = "set_$name";
            return $this->$name($value);
        } elseif (method_exists($this, "set$camelized_name")) {
            $name = "set$camelized_name";
            return $this->$name($value);
        }

        // Otherwise, just use our parent's magic setter
        return parent::__set($name, $value);
    }

    /**
     * Function to format time object attributes
     *
     * @access protected
     * @param string $name
     * @return string The formatted time as a string
     */
    protected function formatTimeAttribute($name)
    {
        return (string) $this->read_attribute($name);
    }

    /**
     * Function to format boolean attributes
     *
     * @access protected
     * @param string $name
     * @return boolean The value of the boolean field
     */
    protected function formatBooleanAttribute($name)
    {
        return (bool) $this->read_attribute($name);
    }

    /**
     * Function to format integer attributes
     *
     * @access protected
     * @param string $name
     * @return integer The value of the integer field
     */
    protected function formatIntegerAttribute($name)
    {
        return (int) $this->read_attribute($name);
    }
}
