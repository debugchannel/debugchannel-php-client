debugchannel-php-client
=======================

php client for DebugChannel.

Installation
============

There 2 ways to install php-client.
- Composer
- Github

Via Composer
------------

php-client is available on composer via [Packagist](https://packagist.org/).
It can be installed as a project dependency using the following command in the project root directory
```
composer require squareproton/debugchannel-php-client
```

Composer will then automagically add debugchannel\DebugChannel to your autoloader. It is that simple.


Via Github
----------

php-client is hosted on GitHub and can be used just like any other local library.
To install php-client download the repository
```
git clone https://github.com/SquareProtonOrg/debugchannel-php-client
```
This will create a directory called `debugchannel-php-client`. You only need to include 1 file - located at `debugchannel-php-client/dist/DebugChannel.php` - and you are done.

For example:
```
<?php
require("path/to/debugchannel-php-client/dist/DebugChannel.php");
...
```

How to use php-client
=====================

The php-client is declared in the namespace `debugchannel` and the client class is called `DebugChannel`.

Initialisation
--------------

```
$d = new debugchannel\DebugChannel("192.168.2.158", "greeting");
```
Creates a new client `$d` pointing at the DebugChannel server found at `192.168.2.158` and displays messages on the channel `greeting`. To view your messages just point a browser at `http://192.166.2.158/greeting`.

Various options can be set with a fourth argument on the constuctor.

```
$d = new debugchannel\DebugChannel(
    "192.168.2.158",
    "greeting",
    null,
    array(
        'expLvl' => 2 // Set the number of intially expanded levels with the object graph
        'maxDepth' => 3 // Sets the recursion depth of with which to explore a object
        'showMethods' => false // switches of display of method signatures on objects
    )
);

The third parameter is currently not used.


Usage
=====

The DebugChannel class has methods to choose how your data is displayed on debugchannel.
The methods are:
- explore: displays a pretty representation of object graphs
- table: displays objects as tables
- string: displays plain text
- image displays images stored in files or base64 encoded images
- code: displays syntax highlighted text
- chat: displays messages like an Instant Messager application with a sender name
- help: display a help message

Explore
-----------------

Great for anything and everything. Pass it whatever and it'll do it's best to display a pretty, type aware, human readable, explorable (where appropriate) and helpful representation of its first argument. A total print_r() and var_dump() replacement.
```
class Person {
  private $name; private $age;
  public __construct($name, $age) {
    $this->name = $name;
    $this->age = $age;
  }
}

$d->explore("Hello World"); // 1
$d->explore(23); // 2
$d->explore(new Person("John", 32)); // 3
$d->explore(array(1,"hello", new Person("John", 32))); // 4
```

Strings
-----------------

Sometimes you just want a string representation of a object. Anything you pass to it will be cast to a string.

```
$d->string("Hello, World!");
```

Clear
-----------------
```
$d->clear()
````
Clears any messages currently being displayed on your channel. Great for 'resetting' a channel at the beginning of a new debugging flow.

Table
-----------------
Have data that looks like it can be displayed as a 2 dimensional table?

like
```
$data = array(
    array(1,2,3),
    array(4,5,6),
    array(7,8,9)
);
$d->table($data);
```

We'll even try handle iterate objects too. So this is also possible.
```
$obj = new stdClass();
$obj->name = 'Peter';
$obj->age = 30;
$obj->likes = 'Php';

$data = array( $obj );

$d->table($data);
```

Code
-----------------
Debugging some sql? Generating some javascript? Want to syntax highlight something?

```
$d->code( "SELECT * FROM something", "sql" );
```

We use the excellent highlight.js syntax highlighting libriary. The list of supported languages can be seen here http://highlightjs.org/download/.

Help
-----------------

Want some help?

```
$d->help();
```
Outputs a helpful message on your channel with links to usage instructions and documentation. Also good for checking if everything is working.

Static Methods and Default Configuration
-----------------

We all want debugging to be as fast a possible. If instantiating a DebugChannel object seems like too much of a chore there are helpful global shortcuts to make this pain go away. No need to add anything to a namespace or muck about with dependency injection. The functions are the the method name from DebugChannel prefixed with 'dc_'; so where you'd have typed `$d->explore($mything)` all you need to do `dc_explore($mything)`. The following all work as you'd expect.

``
dc_clear();
dc_explore("something");
dc_table(...);
dc_code(...)
```

Behind the scenes the `dc_` functions create a new DebugChannel object on first invocation and then reuse it. The constructor arguments for this global object can be set by either configuration file or by calling a function `dc_setup()`.

DebugChannel.php is compliant with PSR-0 autoloaders. The global functions can't / don't use a autoloader so you'll need to manually include the file '/debugchannel/DebugChannel.php'.

dc_setup()
-----------------

Call dc_setup() the same way you might call the constructor for the class DebugChannel. Subsequent calls to any `dc_` function will use a DebugChannel instance configured this way.

eg,
```
dc_setup( 'debugchannel.com', 'channel' );
dc_explore( $mysqlResultSet );
```

Via a configuration file
-----------------

If you don't call `dc_setup()` we search for a config file `dc_setup.json` in the following three directories.

- The same directory the DebugChannel.php file is located in.
- The current working directory.
- The server home directory determined by `$_SERVER['HOME']`.

A setup file not in your projects source control means that each developer can use different channels and set different options based on personal preference without having to modify the code which produces the debugging output.

Documentation
=============

PHP Documentor docs detailing all the class methods are available in the repository. To view it open debugchannel-php-client/doc/index.html in your favourite browser.

