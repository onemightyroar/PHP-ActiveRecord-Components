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

use ActiveRecord\Connection;
use ActiveRecord\Inflector;
use ActiveRecord\Model;
use ActiveRecord\RecordNotFound;
use ActiveRecord\SQLBuilder;
use ActiveRecord\Table;
use ActiveRecord\Utils as ARUtils;
use DateTime;
use OneMightyRoar\PHP_ActiveRecord_Components\Exceptions\ActiveRecordValidationException;
use UnexpectedValueException;

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
     * The format to use when formatting dates or times
     *
     * Should conform to the PHP `date()` format spec:
     * http://www.php.net/manual/en/function.date.php
     *
     * @const string
     */
    const DATE_TIME_FORMAT = DateTime::ISO8601;


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
     * Flag denoting the "freeze" state of the readonly property
     *
     * @var boolean
     * @access private
     */
    private $readonly_frozen = false;


    /**
     * Methods
     */

    /**
     * Create a new readonly model that can't be saved or changed
     *
     * This is a very convenient method for using models as true data/value-objects
     * without having to worry about another service breaking contract and persisting
     * the data represented by the model
     *
     * @see static::__construct()
     * @see static::freezeAsReadonly()
     * @param array $attributes
     * @param mixed $guard_attributes
     * @return AbstractModel
     */
    public static function createAsFrozenReadonly(array $attributes = array(), $guard_attributes = true)
    {
        $model = new static($attributes, $guard_attributes);

        $model->freezeAsReadonly();

        return $model;
    }

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
            throw ActiveRecordValidationException::createFromValidatedModel($this);
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
     * Get a SQL builder instance
     *
     * @param Connection $connection    Optionally injected DB connection
     * @param Table $table              Optionally injected Table instance
     * @static
     * @access public
     * @return SQLBuilder
     */
    public static function getSQLBuilder(Connection $connection = null, Table $table = null)
    {
        $connection = $connection ?: static::connection();
        $table      = $table      ?: static::table();

        return new SQLBuilder(
            $connection,
            $table->get_fully_qualified_table_name()
        );
    }

    /**
     * Get the name of the model that fits AR relational convention
     *
     * @param Table $table      Optionally injected Table instance
     * @param boolean $quoted   Whether or not to quote escape the string with SQL-style quotes
     * @static
     * @access public
     * @return string
     */
    public static function getConventionalRelationName(Table $table = null, $quoted = false)
    {
        $table = $table ?: static::table();

        $table_name = $table->get_fully_qualified_table_name($quoted);

        return ARUtils::singularize($table_name);
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
     * Get the name of the primary key of the model
     *
     * @access public
     * @return string
     */
    public function getKeyName()
    {
        return $this->get_primary_key(true);
    }

    /**
     * Get the value of the primary key of the model
     *
     * @access public
     * @return mixed
     */
    public function getKey()
    {
        return $this->{$this->getKeyName()};
    }

    /**
     * Get the table qualified key name
     *
     * @param Table $table      Optionally injected Table instance
     * @param boolean $quoted   Whether or not to quote escape the string with SQL-style quotes
     * @access public
     * @return string
     */
    public function getQualifiedKeyName(Table $table = null, $quoted = false)
    {
        $table = $table ?: static::table();

        // Get our table and key names
        $table_name = $table->get_fully_qualified_table_name(false);
        $key_name = $this->getKeyName();

        if ($quoted) {
            // Get our connection from our table
            $connection = $table->conn;

            // Quote escape our strings
            $table_name = $connection->quote_name($table_name);
            $key_name = $connection->quote_name($key_name);
        }

        return $table_name .'.'. $key_name;
    }

    /**
     * Check if a given model is a reference to the
     * same ActiveRecord model by checking its model
     * type and its primary key
     *
     * @param AbstractModel $model
     * @access public
     * @return boolean
     */
    public function isSameModel(AbstractModel $model)
    {
        if ($model->getKey() === $this->getKey()
            && $model instanceof static) {

            return true;
        }

        return false;
    }

    /**
     * Check if a given model is equal to this model
     * by checking if they refer to the same AR model
     * and by checking if their attributes are equivalent
     *
     * @param AbstractModel $model
     * @access public
     * @return boolean
     */
    public function isEqual(AbstractModel $model)
    {
        // Check loose equality, since the attributes may
        // contain objects instantiated at different times
        if ($this->attributes() == $model->attributes()
            && $this->isSameModel($model)) {

            return true;
        }

        return false;
    }

    /**
     * Check if a given attribute exists on the model
     *
     * @param string $attribute_name   The name of the attribute to check
     * @param boolean $include_aliases Whether or not to check against aliases too
     * @access public
     * @return boolean
     */
    public function isExistingAttribute($attribute_name, $include_aliases = true)
    {
        $attributes = $this->attributes();

        if ($include_aliases) {
            $attributes = array_merge($attributes, static::$alias_attribute);
        }

        return array_key_exists($attribute_name, $attributes);
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
        // Let's see if this class was called by a child or not
        $extended = (get_class() !== get_called_class());

        // Get our master options (the raw ActiveRecord options, not aliases)
        $order      = isset($paging_options['order'])   ? $paging_options['order']  : null;
        $limit      = isset($paging_options['limit'])   ? $paging_options['limit']  : null;
        $offset     = isset($paging_options['offset'])  ? $paging_options['offset'] : null;

        $per_page   = isset($paging_options['per_page']) ? (int) $paging_options['per_page'] : static::DEFAULT_LIMIT;

        if (isset($paging_options['order_col'])) {
            $order_col = $paging_options['order_col'];
        } elseif (isset($paging_options['order_by'])) {
            $order_col = $paging_options['order_by'];
        } elseif ($extended) {
            $pk = static::table()->pk;
            $order_col = isset($pk[0]) ? $pk[0] : static::DEFAULT_ORDER_COL;
        } else {
            $order_col = static::DEFAULT_ORDER_COL;
        }

        // Let's make sure the table name is present so that this works when there are joins (no amgibuous columns)
        if ($extended && strrpos($order_col, '.') === false) {
            // Add table name and add ticks around order column to protect against reserved words
            $order_col = static::table_name() . '.`' . $order_col . '`';
        }

        if (isset($paging_options['order_desc'])) {
            $order_desc = $paging_options['order_desc'];
        } elseif (isset($paging_options['order_descending'])) {
            $order_desc = $paging_options['order_descending'];
        } else {
            $order_desc = null;
        }

        if (is_null($page)) {
            $page = isset($paging_options['page']) ? (int) $paging_options['page'] : 1;
        }

        // Define our default order option
        if (is_null($order)) {
            if (is_null($order_desc)) {
                $order_dir = static::DEFAULT_ORDER_DIR;
            } else {
                $order_desc = strcasecmp($order_desc, 'false') !== 0;
                $order_dir = $order_desc ? 'DESC' : 'ASC';
            }

            $order = $order_col . ' ' . $order_dir;
        } else {
            // Add ticks around the first word (eg. $order = 'id DESC' becomes '`id` DESC')
            $order = preg_replace('/(?<=\>)\b\w*\b|^\w*\b/', '`$0`', $order);
        }

        if (is_null($limit)) {
            $limit = $per_page;
        }

        if (is_null($offset)) {
            if ($page <= 0) {
                $page = 1;
            }

            $offset = (($page - 1) * $per_page) + static::DEFAULT_OFFSET;
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
        $original_limit  = isset($orig_query_opts['limit'])  ? $orig_query_opts['limit']  : static::DEFAULT_LIMIT;
        $original_offset = isset($orig_query_opts['offset']) ? $orig_query_opts['offset'] : static::DEFAULT_OFFSET;

        // Modify our original options
        $options = array_merge(
            $orig_query_opts,
            array(
                'limit' => 1, // We only care if there's at least one more result
                'offset' => ($original_limit + $original_offset),
                'include' => null, // No need to eager load everything again.
            )
        );

        $number_of_results = count(static::all($options));

        return ($number_of_results > 0);
    }

    /**
     * Get an array of models indexed by each model's given attribute
     *
     * This has a by-product of filtering out any elements in the array
     * that aren't models, as indexing them would be impossible
     *
     * @param array $models          An array of models
     * @param string $attribute_name The name of the attribute to index by
     * @static
     * @access public
     * @throws \UnexpectedValueException If the given attribute name doesn't exist
     * @return array
     */
    public static function indexModelArrayByAttribute(array $models, $attribute_name = null)
    {
        $reindexed_array = [];

        foreach ($models as $model) {
            // Only attempt if we have a model
            if ($model instanceof static) {
                // If we passed an attribute and it doesn't exist...
                if (null !== $attribute_name) {
                    if (!$model->isExistingAttribute($attribute_name)) {
                        throw new UnexpectedValueException('The given attribute '. $attribute_name .' doesn\'t exist');
                    }
                } else {
                    $attribute_name = $model->getKeyName();
                }

                // Get the value of the attribute
                $attribute_value = $model->{$attribute_name};

                $reindexed_array[$attribute_value] = $model;
            }
        }

        return $reindexed_array;
    }

    /**
     * Get an array of models indexed by each model's primary key
     *
     * Semantic alias of self::indexModelArrayByAttribute($models, null);
     *
     * @see self::indexModelArrayByAttribute()
     * @param array $models An array of models
     * @static
     * @access public
     * @return array
     */
    public static function indexModelArrayByKey(array $models)
    {
        return static::indexModelArrayByAttribute($models);
    }

    /**
     * Escape a query parameter value containing wildcards
     *
     * Useful (and important) for when using a `LIKE` query, as not-escaping
     * those queries would allow for a wildcard to be entered by a user and
     * access every result.
     *
     * @param string $parameter_value The value to escape
     * @static
     * @access public
     * @return string
     */
    public static function escapeParameterWildcards($parameter_value)
    {
        return str_replace(
            ['%', '_'],
            ['\%', '\_'],
            $parameter_value
        );
    }

    /**
     * Put the model in a readonly state permanently
     *
     * @access public
     * @return void
     */
    public function freezeAsReadonly()
    {
        // Set as readonly
        $this->readonly(true);

        // Freeze the attribute
        $this->readonly_frozen = true;
    }

    /**
     * {@inheritdoc}
     *
     * @param boolean $readonly Set to true to put the model into readonly mode
     * @access public
     * @return void
     */
    public function readonly($readonly = true)
    {
        // Don't allow them to change the readonly state if its frozen
        if ($this->readonly_frozen) {
            return;
        }

        parent::readonly($readonly);
    }

    /**
     * {@inheritdoc}
     *
     * This is a PSR-2 valid, camel-case style method alias
     *
     * @access public
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->is_readonly();
    }

    /**
     * Assert that a model is valid
     *
     * This will check if a model is valid, and will throw a validation exception
     * if the model is found to have any validation errors
     *
     * @param bool $always_validate Run validations, even if we already know that the model is invalid
     * @access public
     * @throws ActiveRecordValidationException If the model has any validation errors
     * @return void
     */
    public function assertValid($always_validate = true)
    {
        $valid = (null !== $this->errors && $this->errors->is_empty());

        if ($valid || $always_validate) {
            $valid = $this->is_valid();
        }

        if (!$valid) {
            throw ActiveRecordValidationException::createFromValidatedModel($this);
        }
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
     * Determines if an attribute or relationship exists for this Model
     *
     * @see \ActiveRecord\Model::__isset()
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if (parent::__isset($name)) {
            return true;
        }

        $table = static::table();

        return $table->has_relationship($name);
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
        $read_date_time = $this->read_attribute($name);

        if (empty($read_date_time)) {
            return null;
        }

        $date_time = new DateTime($read_date_time);

        return (string) $date_time->format(static::DATE_TIME_FORMAT);
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
