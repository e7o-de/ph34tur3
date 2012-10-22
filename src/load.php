<?php
namespace Ph34tur3;

include(__DIR__ . '/ClassLoader.php');
include(__DIR__ . '/Object.php');

spl_autoload_register(__NAMESPACE__ .'\ClassLoader::loadClass');