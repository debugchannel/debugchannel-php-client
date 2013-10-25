<?php

require("DebugChannel.php");
use debugchannel\DebugChannel;


$d = new DebugChannel("192.168.2.158", "hello");
$d->log("Hello, World!");

