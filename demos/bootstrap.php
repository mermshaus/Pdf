<?php

header('Content-Type: text/html; charset=UTF-8');

if (
    (!$loader = @include __DIR__.'/../../../autoload.php')
    && (!$loader = @include __DIR__.'/../vendor/autoload.php')
) {
    die('You must set up the project dependencies, run the following command:'.PHP_EOL.
        'composer install'.PHP_EOL);
}
