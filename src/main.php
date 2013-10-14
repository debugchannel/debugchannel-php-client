<?php


require __DIR__ . "/../vendor/autoload.php";
use debugchannel\clients\php\D;

print "Hello, World!\n";

$d = new D("192.168.2.18", "hello");
$d->log("Hello from the World!");