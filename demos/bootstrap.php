<?php

error_reporting(-1);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=UTF-8');

if ((!$loader = @include __DIR__.'/../../../autoload.php')
        && (!$loader = @include __DIR__.'/../vendor/autoload.php')
) {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}
