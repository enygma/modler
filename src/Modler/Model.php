<?php

namespace Modler;

class Model
{
    /**
     * Current model properties
     * @var array
     */
    protected $properties = array(
    );

    /**
     * Current model values
     * @var array
     */
    protected $values = array();

    /**
     * Init the object and load data if given
     *
     * @param array $data Data to load [optional]
     */
    public function __construct(array $data = array())
    {
        if (!empty($data)) {
            $this->load($data);
        }
    }

    /**
     * Set the value of a property
     *
     * @param string $name Property name
     * @param mixed $value Property value
     * @throws \InvalidArgumentException If property doesn't exist
     */
    public function __set($name, $value)
    {
        if (!$this->isProperty($name)) {
            throw new \InvalidArgumentException('Property name "'.$name.'" not found');
        }
        $this->values[$name] = $value;
    }

    /**
     * Get the value of a current property
     *
     * @param string $name Property name
     * @return mixed The property value if found, null if not
     */
    public function __get($name)
    {
        $property = $this->getProperty($name);

        if ($property == null) {
            throw new \InvalidArgumentException('Property "'.$name.'" is invalid');
        }

        // See if it's a relation
        if (isset($property['type']) && strtolower($property['type']) == 'relation') {
            return $this->handleRelation($property);
        }

        // If not, probably just a value - return that (or null)
        return (array_key_exists($name, $this->values))
            ? $this->values[$name] : null;
    }

    /**
     * Handle a relational mapping in a model
     *
     * @param array $property Property configuration
     * @return object Instance of relation object (model/collection)
     */
    public function handleRelation($property)
    {
        $model = $property['relation']['model'];
        $method = $property['relation']['method'];
        $local = $property['relation']['local'];

        if (!class_exists($model)) {
            throw new \InvalidArgumentException('Model "'.$model.'" does not exist');
        }

        $instance = $this->makeModelInstance($model);
        if (!method_exists($instance, $method)) {
            throw new \InvalidArgumentException('Method "'.$method.'" does not exist on model '.get_class($instance));
        }
        $params = array(
            (isset($this->values[$local])) ? $this->values[$local] : null
        );
        call_user_func_array(array($instance, $method), $params);
        return $instance;
    }

    /**
     * Make a new model instance
     *
     * @param string $model Model namespace "path"
     * @return object Model instance
     */
    public function makeModelInstance($model)
    {
        $instance = new $model();
        return $instance;
    }

    /**
     * Handle the get* method calls
     *
     * @param string $name Function name called
     * @param array $args Arguments given
     * @return mixed Value if found, null if not
     */
    public function __call($name, $args)
    {
        if (substr($name, 0, 3) == 'get') {
            $property = strtolower(str_replace('get', '', $name));
            if ($this->isProperty($property)) {
                return $this->getValue($property);
            }
        }
        return null;
    }

    /**
     * See if a property is valid
     *
     * @param string $name Property name
     * @return boolean Valid/invalid property
     */
    public function isProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Set a value on the current model
     *
     * @param string $name Property name
     * @param mixed $value Property value
     */
    public function setValue($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Get a value from the current set. If not found,
     *     null is returned
     *
     * @param string $name Property name
     * @return mixed Either the property value or null
     */
    public function getValue($name)
    {
        return (isset($this->values[$name]))
            ? $this->values[$name] : null;
    }

    /**
     * Get the full current property set
     *
     * @return array Property set
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get the configuration for a property. If it doesn't
     *     exist, null is returned
     *
     * @param string $name Property name
     * @return array|null The configuration if found, null otherwise
     */
    public function getProperty($name)
    {
        return (array_key_exists($name, $this->properties))
            ? $this->properties[$name] : null;
    }

    /**
     * Add a property to the list
     *     If exists and override is not set, an exception is thrown
     *
     * @param string $name Property name
     * @param array $config Property configuration
     * @param boolean $override Override existing pproperty [optional]
     * @throws \InvalidArgumentException If property exists and it not overridden
     */
    public function addProperty($name, array $config, $override = false)
    {
        if (array_key_exists($name, $this->properties) && $override === false) {
            throw new \InvalidArgumentException('Property name "'.$name.'" already exists');
        }
        $this->properties[$name] = $config;
    }

    /**
     * Load the given data into the model, checking properties
     *
     * @param array $data Data to load
     */
    public function load(array $data)
    {
        foreach ($data as $name => $value)
        {
            if (array_key_exists($name, $this->properties)) {
                $this->setValue($name, $value);
            }
        }
    }

    /**
     * Return the current set of values in an array
     *
     * @return array Current values
     */
    public function toArray()
    {
        return $this->values;
    }
}
