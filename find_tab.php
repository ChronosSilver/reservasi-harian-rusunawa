<?php
$map = require 'vendor/composer/autoload_classmap.php';
foreach ($map as $class => $path) {
    if (str_contains($class, 'Tab')) {
        echo $class . PHP_EOL;
    }
}
