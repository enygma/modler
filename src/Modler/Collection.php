<?php

namespace Modler;

class Collection implements \Countable, \Iterator
{
    /**
     * Current set of data for collection
     * @var array
     */
    private $data = array();

    /**
     * Current position in data (used in Iterator)
     * @var integer
     */
    private $position = 0;

    // For Countable interface
    /**
     * Return a count of the current data
     *
     * @return integer Count result
     */
    public function count()
    {
        return count($this->data);
    }

    // For Iterator
    /**
     * Return the current item in the set
     *
     * @return mixed Current data item
     */
    public function current()
    {
        return $this->data[$this->position];
    }

    /**
     * Return the current key (position) value
     *
     * @return integer Position value
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Get the next position value
     *
     * @return integer Next position
     */
    public function next()
    {
        return ++$this->position;
    }

    /**
     * Rewind to the beginning of the set (position = 0)
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * See if the requested position exists in the data
     *
     * @return boolean Exists/doesn't exist
     */
    public function valid()
    {
        return isset($this->data[$this->position]);
    }

    /**
     * Add an item to the collection
     *
     * @param mixed $data Data item to add
     */
    public function add($data)
    {
        $this->data[] = $data;
    }

    /**
     * Remove an item from the collection by index ID
     *
     * @param integer $id Item ID
     */
    public function remove($id)
    {
        if (array_key_exists($id, $this->data)) {
            unset($this->data[$id]);
        }
    }

    /**
     * Output the current collection data as an array
     *     If the "expand" is defined, toArray is called on sub-objects too
     *
     * @param boolean $expand Expand sub-elements with toArray too
     * @return array Data set as array output
     */
    public function toArray($expand = false)
    {
        if ($expand == true) {
            $result = array();
            foreach ($this->data as $index => $value) {
                if (is_object($value) && method_exists($value, 'toArray')) {
                    $result[] = $value->toArray();
                } else {
                    $result[] = $value;
                }
            }
            return $result;
        } else {
            return $this->data;
        }
    }

    /**
     * Filter the collection by the boolean return values
     *     in a callable function
     *
     * @param callable $function Filtering function
     * @return \Modler\Collection instance
     */
    public function filter($function)
    {
        $class = get_class();
        $self = new $class();

        foreach ($this->data as $data) {
            if ($function($data) == true) {
                $self->add($data);
            }
        }
        return $self;
    }

    public function slice($start, $items = null)
    {
        $end = ($items !== null) ? $items : count($this->data)-1;
        return array_slice($this->data, $start, $end);
    }
}