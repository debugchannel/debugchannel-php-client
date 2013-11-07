debugchannel-php-client
=======================

php client for DebugChannel.

## Install via Composer ##

php-client is available on composer via [Packagist](https://packagist.org/).
It can be installed as a project dependency using the following command in the project root directory
```
composer require squareproton/debugchannel-php-client
```

Composer will then automagically add debugchannel\DebugChannel to your autoloader. It is that simple.


## Install via Github ##

php-client is hosted on GitHub and can be used just like any other local library.

Download the repository by checking out the code `git clone https://github.com/SquareProtonOrg/debugchannel-php-client` or by downloading a zip `https://github.com/debugchannel/debugchannel-php-client/archive/master.zip`

This will create a directory called `debugchannel-php-client`. You only need to include 1 file - located at `debugchannel-php-client/dist/DebugChannel.php` - and you are done.

For example:
```
<?php
require "path/to/debugchannel-php-client/dist/DebugChannel.php";
...
```

How to use php-client
=====================

The php-client is declared in the namespace `debugchannel` and the client class is called `DebugChannel`. There is a also a small set of global functions.

The different methods on the class DebugChannel determine the way information is displayed on debugchannel.

- ->explore() displays a pretty representation of object graphs
- ->table() displays something as tabular data whenever possible
- ->string() displays plain text
- ->image() displays images stored in files or base64 encoded images
- ->code() displays syntax highlighted text
- ->chat() displays messages like an Instant Messager application with a sender name
- ->help() display a help message

## Initialisation ##

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
```

The third parameter is related to a authentication feature $apiKey which isn't currently used.

## ->explore() ##

Great for anything and everything. Pass it whatever and it'll do it's best to display a pretty, type aware, human readable, explorable (if needed) and helpful representation of its first argument. A totally awesome [`print_r()`](http://php.net/manual/en/function.print-r.php) and [`var_dump`](http://php.net/manual/en/function.var-dump.php) replacement.
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

## ->string() ##

Sometimes you just want a string representation of a object. Anything you pass to it will be cast to a string.

```
$d->string("Hello, World!");
```

## ->clear() ##
```
$d->clear()
````
Clears any messages currently being displayed on your channel. Great for 'resetting' a channel at the beginning of a new debugging flow.

## ->table() ##
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

## ->code() ##
Debugging some sql? Generating some javascript? Want to syntax highlight something?

```
$d->code( "SELECT * FROM something", "sql" );
```

We use the excellent highlight.js syntax highlighting libriary. The list of supported languages can be seen here http://highlightjs.org/download/.

## ->help() ##

Want some help?

```
$d->help();
```
Outputs a helpful message on your channel with links to usage instructions and documentation. Also good for checking if everything is working.

## Options ##

All the functions which send output to the debug server can take a few options. These are

  - die; terminate script execution immediately after sending a message. Exits with code 0.
  - expand; chose whether to expand object when passed to ->explore(). At the time of writing this isn't yet implemented in the webapp.

Debug channel defines two constants - `DC_DIE` and `DC_EXPAND` - to express these. You can pass options as either a array or you can get your bitwise operator on. The following two explore lines are equivalent.

```
$d = new DebugChannel('localhost', 'mychannel');

$d->explore( "...", array(DC_DIE, DC_EXPAND) );
$d->explore( "...", DC_DIE | DC_EXPAND );
```

There is also a shortcut for the 'die' functionality. Prepend the `!` to any of the methods which cause a debug message to be sent. The following are equivalent.

```
!$d->explore("I'm going to exit");
$d->explore("I'm going to exit", [DC_DIE] );
$d->explore("I'm going to exit", DC_DIE );
```

The complete list of functions this operates on is `!dc_explore()`, `!dc_table()`, `!dc_string()`, `!dc_code()`, `!dc_image()`, `!dc_chat()`, `!dc_clear()`, `!dc_help()`, `!$dc->explore()`, `!$dc->table()`, `!$dc->string()`, `!$dc->code()`, `!$dc->image()`, `!$dc->chat()`, `!$dc->clear()`, `!$dc->help()`.

## Static Methods and Default Configuration ##

We all want debugging to be as fast a possible. If instantiating a DebugChannel object seems like too much of a chore there are helpful global shortcuts to make this pain go away. No need to add a `uses` statement or worry about namespaces, muck about with dependency injection, ... .

The global functions are simply the method name from DebugChannel prefixed with 'dc_'; so where you'd have typed `$d->explore($mything)` all you need to do now is `dc_explore($mything)`. The following all work as you'd expect.

```
dc_clear();
dc_explore(...);
dc_table(...);
dc_code(...)
```

Behind the scenes the `dc_*` functions create a new DebugChannel object on first invocation and then reuse it. The constructor arguments for this global object can be set by either configuration file or by calling a function `dc_setup()`.

DebugChannel.php is compliant with PSR-0 autoloaders. The global functions can't / don't use a autoloader so you'll need to manually include the file 'debugchannel/DebugChannel.php' in a bootstrap-esque file somewhere.

## dc_setup() ##

Call dc_setup() the same way you might call the constructor for the class DebugChannel. Subsequent calls to any `dc_*` functions will use a DebugChannel instance configured this way.

eg,
```
dc_setup( 'debugchannel.com', 'channel' );
dc_explore( $mysqlResultSet );
```

## Via a configuration file ##

If you don't call `dc_setup()` we search for a config file `dc_setup.json` in the following three directories.

- The same directory the DebugChannel.php file is located in.
- The current working directory.
- The server home directory determined by `$_SERVER['HOME']`.

A setup file not in your projects source control means that each developer can use different channels and set different options based on personal preference without having to modify the code which produces the debugging output.

The setup file must be valid json and it's structure is as follows.

```
{
    "host": "192.168.2.17",
    "channel" : "peter",
    "apiKey": null,
    "options": {
      "showPrivateMembers": true,
      "expLvl": 2,
      "maxDepth": 2,
      "showMethods": false
    }
}
```

Want more?
=============

Awesome. PHP Documentor docs detailing all the class methods and a lot more techie detail are available in the repository. To view it open debugchannel-php-client/doc/index.html in your favourite browser.