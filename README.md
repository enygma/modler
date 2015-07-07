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
        "enygma/modler": "1.*"
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

#### Relational Properties

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

#### Relations "Return Value"

You can also have a relation return a value instead of an instance of a class or model. To do so, just set the `return` value to "value" and use the normal `return` from your method:

```php
<?php
class TestModel extends \Modler\Model
{
    'property1' => array(
        'description' => 'Property #1',
        'type' => 'varchar'
    ),
    'relateMe' => array(
        'description' => 'Return by value relation',
        'type' => 'relation',
        'relation' => array(
            'model' => '\\MyApp\\OtherModel',
            'method' => 'findByTestReturnValue',
            'local' => 'test',
            'return' => 'value'
        )
    )
}

class OtherModel extends \Modler\Model
{
    public function findByTestReturnValue($property1)
    {
        return 'this is a value';
    }
}

// Then, in the "relateMe" property we'd get...
if ($testModel->relateMe === 'this is a value') {
    echo 'Valid match!';
}

?>
```

In this case we're calling `\MyApp\OtherModel::findByTestReturnValue` and asking for a return value rather than getting back the model with a value set.

#### Data Verification

Modeler can also do some simple data verification around required values. You can set the required state in the property configuration:

```php
<?php
class OtherModel extends \Modler\Model
{
    protected $properties = array(
        'prop1' => array(
            'description' => 'Property #1 (Required)',
            'type' => 'varchar',
            'required' => true
        )
    );
}

$other = new OtherModel();
try {
    $other->verify();
} catch (\Exception $e) {
    echo 'ERROR: '.$e->getMessage();
}
?>
```

If a value is not set (checked with `isset`) an exception will be thrown with information on which property was missing. If you ever find a place where you're getting an exception but you want a value ignored from the verification, you can use the optional parameter to the `verify` method:

```
<?php

$ignore = array('prop1');
try {
    $other->verify($ignore);
    echo 'Success!';
} catch (\Exception $e) {
    echo 'Never gets here.';
}

?>
```

### Using the MySQL Model

Modler also comes with a MySQL version of the model class that contains some common database functionality. It includes some of the basics you'd need for working with models and loading them from a database. It follows a sort of Active Record methodology with a few differences. Here's how to make a simple MySQL model:

```php
<?php

class UserModel extends \Modler\Model\Mysql
{
    protected $tableName = 'users';

    protected $properties = [
        'username' => ['column' => 'username']
    ];
}

?>
```

You can see a few key differences here: first is the `tableName` property defining what MySQL table the model needs to work with, second is the `column` attribute in the `properties`. This links the field to a column in the database and lets Modler know where to load the data.

You can then use most of the expected functionality to load and work with objects:

```php
<?php

$db = new PDO('mysql:hostname=127.0.0.1;dbname="testdb', 'username', 'password');
$user = new UserModel($db);

// Finding a user by the "id" column, loads the data into the user
$user->findById(1);

// Or using custom criteria
$user->find(['username' => 'foobar']);

// Creating a new user
$user = new UserModel($db);
$user->username = 'newuser1';
$user->save();

// Updating the username property and saving
$user->username = 'foobarbaz';
$user->save();

// Deleting a user
$user = new UserModel($db);
$user->findById(1);
$user->delete();
?>
```

You'll notice that, before we can do anything with a user object, we need to call one of the two `find` methods on it, either `findById` or just `find`. This will locate the user details and pull them into the current object. Otherwise Modler has no idea what to operate on.

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

Much like in the models, custom collections methods will probbly not want to return a value. Insted they should populate out the data where they live. See the next section for an example that'll probably make more sense.

### Using the MySQL Collection

Much like the MySQL model that's included with Modler, there's also a simple MySQL collection. Mostly it's a convenience method for getting data and making it eaier to load into the database. As seen in the example below (`findColorsByType`), you can easily push the results of a query into a set of objects and add them to the collection. Here's an example:

```php
<?php

class UserCollection extends \Modler\Collection\Mysql
{
    public function findUsersByType($type)
    {
        $sql = 'select username from users where type = :type';
        $results = $this->fetch($sql, array('type' => $type));

        foreach ($results as $result) {
            $user = new Usermodel($this->getDb(), $result);
            $this->add($user);
        }
    }
}

?>
```

You'll then have a set of `UserModel` objects that match the type you've queried for. Note that the query handling uses *bound parameters/prepared statements* for execution.

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

### Filtering

Collections also allow you to do some basic filtering based on custom logic. You can use the `filter` method to to a pass/fail check on the
data and add/remove things from the collection. Here's an example:

```php
<?php

$this->collection->add(array('foo' => 'bar'));
$this->collection->add(array('baz' => 'test'));

$filtered = $this->collection->filter(function($value) {
    return (isset($value['foo']));
});

print_r($filtered);

?>
```

In this case, we're checking to see if the `foo` array key is set. It's only set in the one case (with the value of "bar") so the resulting
collection in `filtered` will only contain this one element. The callable function you pass in should take one parameter, the value, and
should return a boolean for the pass/fail status of the check. Values cannot be modified through this method.

### Slicing

You can also get portions of the collection data without having to export it all using `toArray` and working with it there. The `slice` method
makes it easy to get just the portion of the data you want. The first parameter is the start index and the second (optional) is how many items
to return. It works using the [array_slice](http://php.net/array_slice) function.

````php
<?php

$this->collection->add('foo');
$this->collection->add('bar');
$this->collection->add('baz');
$this->collection->add('test');

// This will return: array('bar', 'baz', 'test')
$sliced = $this->collection->slice(1);

// This will return: array('baz')
$sliced = $this->collection->slice(2, 1);

?>
```

### Guarding and Array Filtering

`Modler` also includes two other concepts around the contents of the model data: guarding and filtering (not the same as the `filter` method above).

**Guarding** lets you protect properties from being set when the `load` method is called or when set as a property on the object. This helps to prevent mass assignment security issues. For example, we make a model with two properties and guard the `id` value so it can never be overriden:

```php
<?php

class HatModel extends \Modler\Model
{
    protected $properties = array(
        'id' => array(
            'description' => 'Hat ID',
            'type' => 'integer',
            'guard' => true
        ),
        'type' => array(
            'description' => 'Hat Type',
            'type' => 'varchar'
        )
    );
}

// Now if we try to set it...
$hat = new HatModel();
$hat->id = 1234;

var_export($hat->id); // result is NULL

// Calling load has the same effect
$hat->load(array('id' => 1234));

?>
```

There is a second optional paramater for the `load` method that turns off this guarding and allows the setting of all values (useful when loading objects from a datasource):

```php
<?php

$data = array('id' => 1234);
$hat->load($data, false);

var_export($hat->id); // result is 1234
?>
```

The other feature, array filtering, works with the `toArray` method and lets you filter out values when you call it. This can be useful if you have an object with sensitive data like password hashes that don't need to be stored. Here's an example:

```php
<?php

class UserModel extends \Modler\Model
{
    protected $properties = array(
        'username' => array(
            'description' => 'Username',
            'type' => 'varchar'
        ),
        'password' => array(
            'description' => 'Password',
            'type' => 'varchar'
        )
    );
}

// To get the result without the password, we call it with the filter paramater
$user = new UserModel(
    array('username' => 'foobar', 'password' => password_hash('foobar'))
);

$result = $user->toArray(array('password'));
print_r($result); // results in an array with just array('username' => 'foobar')
?>
```

### Array "taking" and ordering

There's also a similar interface to the `slice` method on Modler collections called `take`. With slice you can get more specific with the section of the results but if you need something simpler, you can just "take" a few off the top:

```php
<?php
// Add a bunch of items to our collection
$data = array('foo', 'bar', 'baz', 'quux', 'foobar', 'barbaz');
$collection = new \Modler\Collection();
foreach ($data as $value) {
    $collection->add($value);
}

// You can limit the current collection and return
$collection = $collection->take(3);

 // this returns 3
echo 'count: '.count($collection);

// This returns Array('foo', 'bar', 'baz')
print_r($collection->toArray());

?>
```

**NOTE:** The collection returned is a new instance. The data in the previous collection is untouched.

You can also *sort* the data in the current collection (this does modify the collection dataset) either as a set of strings or by a property on an object. First, as strings:

```php
<?php
// Add a bunch of items to our collection
$data = array('foo', 'bar', 'baz', 'quux', 'foobar', 'barbaz');
$collection = new \Modler\Collection();
foreach ($data as $value) {
    $collection->add($value);
}

$collection->order();

// returns Array('bar', 'barbaz', 'baz', 'foo', 'foobar', 'quux');
print_r($collection->toArray());
?>
```

The default sort order is descending, but you can tell it to sort ascending:

```php
<?php
$collection->order(\Modler\Collection::SORT_ASC);
?>
```

And, if you want to sort objects, you provide the name of the property as the second parameter:

```php
<?php
// Add a bunch of items to our collection
$data = array('foo', 'bar', 'baz', 'quux', 'foobar', 'barbaz');
$collection = new \Modler\Collection();
foreach ($data as $value) {
    $collection->add($value);
}

$collection->order(\Modler\Collection::SORT_DESC, 'test');

/**
 * Returns set of objects ex:
 * [0] => stdClass Object
 *  ( [test] => bar )
 *
 * [1] => stdClass Object
 *  ( [test] => barbaz )
 *
 * [2] => stdClass Object
 *  ( [test] => baz )
 */
print_r($collection->toArray());
?>
```


