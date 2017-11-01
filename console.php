<?php

$params = $argv;
array_shift($params);

$cmd = array_shift($params);

switch ($cmd) {
    // doesn't work yet
    case 'dev': {
        $pattern = '/moota*/';

        $dir = new RecursiveDirectoryIterator(__DIR__);
        $ite = new RecursiveIteratorIterator($dir);
        $subDirFiles = new RegexIterator(
            $ite, $pattern, RegexIterator::GET_MATCH
        );

        $files = array();

        foreach($subDirFiles as $file) {
            $files = array_merge($files, $file);
        }

        var_dump(array_unique($files));
    }
}
