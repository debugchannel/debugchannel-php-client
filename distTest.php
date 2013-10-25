<?php

use debugchannel\DebugChannel;

require __DIR__ ."/src/debugchannel/DebugChannel.php";

$d = new DebugChannel('localhost', 'disttest');

$d->explore("something");
$d->table([[1,2,3],[1,2,3]]);