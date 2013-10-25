<?php

use debugchannel\D;

require __DIR__ ."/dist/D.php";

$d = new D('localhost', 'disttest');

$d->explore("something");