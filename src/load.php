<?php
namespace Ph34tur3;

include(__DIR__ . '/ClassLoader.php');
include(__DIR__ . '/Object.php');
include(__DIR__ . '/Error.php');

spl_autoload_register(__NAMESPACE__ .'\ClassLoader::loadClass');