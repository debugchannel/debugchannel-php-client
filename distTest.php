<?php

use debugchannel\DebugChannel;

require __DIR__ ."/src/debugChannel/DebugChannel.php";

echo __DIR__ ."/src/debugChannel/DebugChannel.php";
die();

$d = new DebugChannel('localhost', 'disttest');

$d->explore("something");