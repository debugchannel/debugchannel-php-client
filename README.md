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

Usage
=====

The DebugChannel class has methods to choose from when deciding how to display your data.
The methods are:
- explore: displays a pretty representation of object graphs
- table: displays objects as tables
- string: displays plain text
- image displays images stored in files or base64 encoded images
- code: displays syntax highlighted text
- chat: displays messages like an Instant Messager application with a sender name

Each method is designed to show a particular type of data in the most appropriate manner possible.

Example - explore
-----------------
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


Example - strings
-------------------
```
$d->string("Hello, World!");
```
This will send the message `Hello, World`. You can pass anything to this method and it'll be cast to a string.

Example 2 - Objects
-------------------
```
$myobject = new ...
$d->explore($myobject);
```
The object will be converted to a graph in the browser allowing you to navigate around inspecting properties and methods.


Example 3 - Clear
-----------------
```
$d->clear()
````
Clears any messages currently being displayed. Great for 'resetting' a channel at the beginning of a new debugging flow.

Documentation
=============
The documentation is available in the repository, to view it open debugchannel-php-client/doc/index.html in your favourite browser.
