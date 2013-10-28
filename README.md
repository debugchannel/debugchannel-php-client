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
composer require squareproton/debuchannel-php-client
```


Via Github
----------

php-client is hosted on GitHub and can be used as a local library.
To install php-client download the repository
```
git clone https://github.com/SquareProtonOrg/debugchannel-php-client
```
this will create a directory in working directory called `debugchannel-php-client`.
You will only need to include 1 file to use the php-client, that is located at `debugchannel-php-client/dist/DebugChannel.php`.
For example:
```
<?php
require("path/to/debugchannel-php-client/dist/DebugChannel.php");
...
```

How to use php-client
=====================

The best way to show how to use something is by example, therefore:

Initialisation
--------------

```
$d = new debugchannel\DebugChannel("192.168.2.158", "greeting");
```
Initialises DebugChannel to `192.1682.158/greeting` where `greeting` is the channel being published on.


Example 1 - Strings
-------------------
```
$d->string("Hello, World!");
```
This will send the message `Hello, World`


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
A helpful method of cleaning a channel so it's completely empty for all users viewing that channel.


Documentation
=============
The documentation is available in the repository, to view it open debugchannel-php-client/doc/index.html in your favourite browser(Chrome).
