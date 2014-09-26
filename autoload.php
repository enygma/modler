<?php
spl_autoload_register(function ($class) {
    // what prefixes should be recognized?
    $prefixes = array(
        "Modler\\" => array(
            __DIR__ . '/src/Modler',
            __DIR__ . '/tests/Modler',
        )
    );

    // go through the prefixes
    foreach ($prefixes as $prefix => $dirs) {

        // does the requested class match the namespace prefix?
        $prefix_len = strlen($prefix);
        if (substr($class, 0, $prefix_len) !== $prefix) {
            continue;
        }

        // strip the prefix off the class
        $class = substr($class, $prefix_len);

        // a partial filename
        $part = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

        // go through the directories to find classes
        foreach ($dirs as $dir) {
            $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
            $file = $dir . DIRECTORY_SEPARATOR . $part;
            if (is_readable($file)) {
                require_once $file;
                return;
            }
        }
    }

});
