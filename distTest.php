<?php

use debugchannel\DebugChannel;
use debugchannel\D;

require __DIR__ ."/src/debugchannel/DebugChannel.php";

$d = new DebugChannel('localhost', 'disttest');

foreach( range(1,1) as $n ) {
    $d->explore(str_repeat('! ', 1024*1024 +1 ));
}
//$d->explore(range(1,50));