<?php
/**
 * PHP-ActiveRecord-Components
 *
 * Useful common components for a php-activerecord based project
 *
 * @copyright   2013 One Mighty Roar
 * @link        http://onemightyroar.com
 */

namespace OneMightyRoar\PHP_ActiveRecord_Components\RedisIntegration;

use \OneMightyRoar\PHP_ActiveRecord_Components\AbstractModel;
use \OneMightyRoar\PHP_ActiveRecord_Components\Exceptions\ReadOnlyAttributeException;
use \OneMightyRoar\PredisToolkit\TransactionClient;

/**
 * AbstractRedisIntegrationModel
 *
 * Base model for models that have an extra Redis component
 * This allows for easy and automatic redis integration when an
 * ActiveRecord model saves successfully
 *
 * @uses \OneMightyRoar\PHP_ActiveRecord_Components\AbstractModel
 * @abstract
 * @package
 */
abstract class AbstractRedisIntegrationModel extends AbstractModel
{

    /**
     * The redis Transaction Client
     *
     * @var TransactionClient
     * @access protected
     */
    protected $redis;


    /**
     * Constructor
     *
     * @see \OneMightyRoar\PHP_ActiveRecord_Components\AbstractModel::__construct()
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
        // Call our parent constructor AFTER we've merged our static attributes
        parent::__construct($attributes, $guard_attributes, $instantiating_via_find, $new_record);

        $this->redis = new TransactionClient();
    }

    /**
     * Save the model to the database
     *
     * Process any queued Redis commands to save in Redis
     * if the model saving was a success
     *
     * @see \ActiveRecord\Model::save()
     * @param boolean $validate Set to true or false depending on if you want the validators to run or not
     * @return boolean True if the model was saved to the database otherwise false
     */
    public function save($validate = true)
    {
        $success = parent::save($validate);

        if ($success) {
            // The save worked, so let's also save to redis.
            $this->redis->process_queue();
        }

        return $success;
    }

    /**
     * Attempt to set the redis object
     *
     * The redis attribute is read-only, so an exception is thrown.
     *
     * @access protected
     * @throws \OneMightyRoar\PHP_ActiveRecord_Components\Exceptions\ReadOnlyAttributeException
     */
    protected function setRedis()
    {
        throw new ReadOnlyAttributeException('Redis attribute is read-only');
    }
}
