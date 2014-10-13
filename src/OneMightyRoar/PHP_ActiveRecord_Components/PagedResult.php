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
 * PagedResult
 *
 * An object to contain a model's multiple
 * result data and its properties
 *
 * @package OneMightyRoar\PHP_ActiveRecord_Components
 */
class PagedResult
{

    /**
     * The current page
     *
     * @var int
     * @access protected
     */
    protected $page = 1;

    /**
     * The order statement
     *
     * @var string
     * @access protected
     */
    protected $order;

    /**
     * The limit of the returned response
     *
     * @var int
     * @access protected
     */
    protected $limit;

    /**
     * The offset of the query
     *
     * @var int
     * @access protected
     */
    protected $offset;

    /**
     * Do we have a "next" results page?
     *
     * @var int
     * @access protected
     */
    protected $has_next_page = false;

    /**
     * The actual data of the response
     *
     * @var array
     * @access protected
     */
    protected $data;


    /**
     * Constructor
     *
     * @param array $data
     * @param int $page
     * @param bool $has_next_page
     * @param array $paging_options
     * @access public
     */
    public function __construct($data, $page, $has_next_page, $paging_options = null)
    {
        $this->setData($data);
        $this->setPage($page);
        $this->setHasNextPage($has_next_page);
        $this->setPagingProperties($paging_options);
    }

    /**
     * Gets the value of page
     *
     * @access public
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }
    
    /**
     * Sets the value of page
     *
     * @param int $page
     * @access public
     * @return PagedResult
     */
    public function setPage($page)
    {
        if (is_null($page)) {
            return false;
        }

        $this->page = (int) $page;
        return $this;
    }

    /**
     * Gets the value of order
     * @access public
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Sets the value of order
     *
     * @param string $order
     * @access public
     * @return PagedResult
     */
    public function setOrder($order)
    {
        $this->order = (string) $order;
        return $this;
    }

    /**
     * Gets the value of limit
     *
     * @access public
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Sets the value of limit
     *
     * @param int $limit
     * @access public
     * @return PagedResult
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * Gets the value of offset
     *
     * @access public
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * Sets the value of offset
     *
     * @param int $offset
     * @access public
     * @return PagedResult
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Gets the value of has_next_page
     *
     * @access public
     * @return boolean
     */
    public function getHasNextPage()
    {
        return $this->has_next_page;
    }
    
    /**
     * Sets the value of has_next_page
     *
     * @param boolean $has_next_page
     * @access public
     * @return PagedResult
     */
    public function setHasNextPage($has_next_page)
    {
        $this->has_next_page = (bool) $has_next_page;
        return $this;
    }

    /**
     * Gets the value of data
     *
     * @access public
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Sets the value of data
     *
     * @param array $data
     * @access public
     * @return PagedResult
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Mass set the paging properties
     *
     * @param array $paging_properties
     * @access public
     * @return void
     */
    public function setPagingProperties(array $paging_properties)
    {
        $mass_settable_properties = array(
            'order',
            'limit',
            'offset',
        );

        // Drop the keys we don't want
        $props = array_intersect_key(
            $paging_properties,
            array_flip($mass_settable_properties)
        );

        // Make sure there are no "unset" keys
        $props = array_merge(
            array_flip($mass_settable_properties),
            $props
        );

        // Set the things...
        $this->setOrder($props['order']);
        $this->setLimit($props['limit']);
        $this->setOffset($props['offset']);
    }

    /**
     * Was our query result returned in a descending order
     *
     * @access public
     * @return boolean
     */
    public function isDescendingOrder()
    {
        $order_string = $this->getOrder();

        $desc_count = 0;
        $asc_count = 0;

        $order_parts = explode(',', $order_string);

        foreach ($order_parts as $order) {
            if (stristr($order, 'desc')) {
                $desc_count++;
            } else {
                $asc_count++;
            }
        }

        return ($desc_count > $asc_count);
    }
}
