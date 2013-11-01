<?php

use debugchannel\DebugChannel;
use debugchannel\D;

require __DIR__ ."/src/debugchannel/DebugChannel.php";

/*
foreach( range(1,1) as $n ) {
    $d->explore(str_repeat('! ', 1024*1024 +1 ));
}
*/

$d = new DebugChannel('192.168.2.17', 'peter');

$a = 'a';
$b = 'b';


$d->explore($a); echo "a\n"; !dc_explore($b); echo "b\n";
