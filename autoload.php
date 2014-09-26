<?php
spl_autoload_register(function ($class) {
    $dirs = array(
        __DIR__ . '/src',
        __DIR__ . '/tests',
    );

    foreach ($dirs as $dir) {
        // a partial filename
        $part = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        $file = $dir . DIRECTORY_SEPARATOR . $part;
        if (is_readable($file)) {
            require_once $file;
            return;
        }
    }

});
