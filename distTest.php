<?php

require __DIR__ ."/src/DebugChannel.php";

/*
foreach( range(1,1) as $n ) {
    $d->explore(str_repeat('! ', 1024*1024 +1 ));
}
*/

$d = new DebugChannel('https://debugchannel.com', 'pete');

$a = 'a';

$d->explore(rand());
