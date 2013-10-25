<?php

use debugchannel\DebugChannel;
use debugchannel\D;

require __DIR__ ."/src/debugchannel/DebugChannel.php";

$d = new DebugChannel('localhost', 'disttest');

$d->explore("via DebugChannel");
$d->table([[1,2,3],[1,2,3]]);

$d = new D('localhost', 'disttest');
$d->explore("via D");
