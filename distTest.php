<?php

use debugchannel\DebugChannel;

require __DIR__ ."/dist/DebugChannel.php";

$d = new DebugChannel('localhost', 'disttest');

$d->explore("something");