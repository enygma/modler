Modler: a generic model layer for PHP
=============

Modler is a set of scripts that provide some of the most basic model and collection handling
pieces of functionality.

## Classes

### Model

The Model class helps to model out an object with properties and relationships. A model is defined
with a set of `properties` that can either be literal values or relationships to other models. Here's
an example of a simple model:

```php
<?php

class TestModel extends \Modler\Model
{
    protected $properties = array(
        'test' => array(
            'description' => 'Test Property 1'
            'type' => 'varchar'
        ),
        'relateMe' => array(
            'description' => 'Relation Property 1',
            'type' => 'relation',
            'relation' => array(
                'model' => '\\MyApp\\OtherModel',
                'method' => 'findByTestValue',
                'local' => 'test'
            )
        )
    );
}

?>
```

In this example there's two properties set: `test` and `relateMe`. The `test` is a simple property, allowing
for easy getting and setting of the data:

```php
<?php

$model = new TestModel();
$model->test = 'foo';

?>
```

The relational property `relateMe` is a little more complicated. It's defined as a type of "relation" and the `relation`
configuration contains three pieces of information:

- The `model` to call (full namespacing is better here)
- The `method` to call on the other model
- The `local` property to send as a parameter

All properties are lazy loaded, so you're only going to execute the call on `relateMe` if you use it in your code:

```php
<?php

$model = new TestModel();
$otherModel = $model->relateMe;

?>
```

When the `relateMe` property is used, the `findByTestValue` method is called on an instance of the `OtherMethod` class with
the value currently in the `test` property. If none is defined, `null is passed`. The resulting model is then returned. So,
you can do fun chaining things like:

```php
<?php

$model = new TestModel();
echo $model->relateMe->otherModelProperty;

?>
```

If the `findByTestValue` method uses the `load` method inside of it to load the data, you should get a value back if it's found:

```php
<?php

class OtherModel extends \Modler\Model
{
    protected $properties = array(
        'otherModelProperty' => array(
            'type' => 'varchar',
            'description' => 'Just another property'
        )
    );

    public function findByTestValue($value)
    {
        /** some SQL that extracts the data */
        $this->load($result);
    }
}

?>
```

Remember, when you use the properties, you're going to get back the updated model instance, not the return value for the method that was called.
You'd need to access properties on the returned model to get data from it.


### Collection

The Collection class is designed to be a set of models. It's a generic container that can be
iterated over.

@todo