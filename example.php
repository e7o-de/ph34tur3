<?php
include('src/load.php');

$c = new \Example\C;

echo $c->getA();
echo $c->getB();

$c->aFunction = function() { echo 'Hello World!'; };
$c->aFunction();

$c->checkParents();

$c->giveMeIntegers(4);
echo '... and now an error:';
$c->giveMeIntegers('abc');