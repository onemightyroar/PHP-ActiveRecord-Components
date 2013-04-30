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

use \ActiveRecord\Inflector;

/**
 * SluggableModelTrait
 *
 * Utility mix-in abilities for adding SLUG behaviors to a model
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
trait SluggableModelTrait
{

    /*
     * Trait properties
     */
    protected $slug_verification_tried = false;

    /**
     * Generate a "slug" from the given string
     *
     * @param string $string The string to generate a slug from
     * @access public
     * @return string
     */
    public function generateSlug($string)
    {
        $slug = Inflector::instance()->underscorify($string);

        return strtoupper($slug);
    }

    /**
     * Generate a "slug" attribute from the current model's "name" attribute
     *
     * @param boolean $overwrite Whether or not to overwrite the current slug
     * @access public
     * @return string
     */
    public function generateSlugAttributeFromName($overwrite = false)
    {
        if (null === $this->slug || $overwrite) {
            // Use our generic generator
            $this->slug = $this->generateSlug($this->name);
        }

        return $this->slug;
    }

    /**
     * Verifies that the slug is unique and automatically recreates a slug if need be
     *
     * This method simply checks if a slug is invalid, and attempts to create a better
     * slug by modifying the current slug and appending a number based on the last similar slug
     *
     * @param boolean $save Whether or not to save after modifying the slug
     * @access public
     * @return boolean
     */
    public function verifyAndCreateUniqueSlug($save = false)
    {
        // If the slug is invalid, let's try to autogenerate a unique one
        if (!$this->slug_verification_tried && null !== $this->errors && $this->errors->is_invalid('slug')) {
            // Grab the last model row with a similar slug
            $last = static::last(
                array(
                    'conditions' => array(
                        'slug LIKE ?',
                        $this->slug . '%',
                    )
                )
            );

            // Grab the parts of the slug
            $matched = preg_match('/_([0-9]+)$/', $last->slug, $matches);

            if ($matched) {
                // Create a new slug suffix
                $slug_suffix = '_' . ((int) $matches[1] + 1);
            } else {
                // Create a new slug suffix
                $slug_suffix = '_' . 1;
            }

            // Append our suffix
            $this->slug .= $slug_suffix;

            // Keep track of this running
            $this->slug_verification_tried = true;

            // Invoke validation
            $now_valid = $this->is_valid();

            if ($save) {
                return $this->update();
            }
        }

        return true;
    }
}
