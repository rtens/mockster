<?php

function autoloader($classname) {
    $file = __DIR__ . '/src/' . $classname . '.php';
    if (file_exists($file)) {
        include $file;
    }
}

spl_autoload_register('autoloader');