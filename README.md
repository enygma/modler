Modler: a generic model layer for PHP
=============

[![Build Status](https://secure.travis-ci.org/enygma/modler.png?branch=master)](http://travis-ci.org/enygma/modler)

Modler is a set of scripts that provide some of the most basic model and collection handling
pieces of functionality.

## Installation

You can install the library via Composer (in your `composer.json` file):

```
{
    "require": {
        "enygma/modler": "1.0"
    }
}
```

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
iterated over. There's no special logic in it that requires the contents to be a model (or even
and object for that matter).

They're pretty straightforward to use:

```php
<?php

$hats = new HatsCollection();
$hats->add(array('test' => 'foo'));

foreach ($hats as $value) {
    print_r($value); // will output the "test" => "foo" data
}

echo 'Items in collection: '.count($hats)."\n";

?>
```

There is one interesting thing that you can do with the Modler models if you use them in this collection. The example
above mentions the `toArray` method on the Model class. The Collection has one too, but it includes an `expand` parameter.
If this parameter is set to `true` (`false` by default) it will call the `toArray` method on each item in the collection and
return those results. For example:

```php
<?php
$hat1 = new HatModel();
$hat1->type = 'fedora';

$hat2 = new HatModel();
$hat2->type = 'sombrero';

$hats = new HatsCollection();
$hats->add($hat1);
$hats->add($hat2);

$result = $hats->toArray(true);

/**
 * Result will be array(
 *     array('type' => 'fedora'),
 *     array('type' => 'sombrero')
 * )
 */
?>
```

The `Hats` models will have the `toArray` called on them too, translating them into their array versions and appended for output.

Much like in the models, custom collections methods will probbly not want to return a value. Insted they should populate out the data
where they live. See the next section for an example that'll probably make more sense.

## Combining them

Lets combine them using the relations the models offer and populate a collection:

```php
<?php

class HatsCollection extends \Modler\Collection
{
    public function findColorsByType($type)
    {
        foreach ($results as $result) {
            $color = new \Modler\Color($result);
            $this->add($color);
        }
    }
}

class HatModel extends \Modler\Model
{
    protected $properties = array(
        'type' => array(
            'description' => 'Hat Type',
            'type' => 'varchar'
        ),
        'colors' => array(
            'description' => 'Hat Colors',
            'type' => 'relation',
            'relation' => array(
                'model' => '\\Modler\\HatsCollection',
                'method' => 'findColorsByType',
                'local' => 'type'
            )
        )
    );
}

$hat = new HatModel();
$hat->type = 'toque';

// This returns a Hats Collection with the data populated
$colors = $hat->colors;

?>
```

Now when we access the `$hat->colors` property, we'll get back an instance in `$colors` of the `HatsCollection` with the data loaded.
You can then use it just like a normal collection and use `toArray` on it to get the contents.

