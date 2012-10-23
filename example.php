<?php

include('src/load.php');

$c = new \Example\C;

echo $c->getA();
echo $c->getB();

$c->aFunction = function() { echo 'Hello World!'; };
$c->aFunction();