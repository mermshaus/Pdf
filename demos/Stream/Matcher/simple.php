<?php

use mermshaus\Stream\Matcher\CharTest;
use mermshaus\Stream\Matcher\EofTest;
use mermshaus\Stream\Matcher\Matcher;
use mermshaus\Stream\Matcher\StringTest;

require __DIR__ . '/../../bootstrap.php';

$matcher = new Matcher();




$data = <<<'EOT'
Axxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
hallo welt
xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxUUUUZ
EOT;

$dataStream = 'data://text/plain;base64,' . base64_encode($data);

$handle = fopen($dataStream, 'rb');

$tests = array(
    new CharTest('/Z/', '*'),
    new CharTest('/A/'),
    new CharTest('/x/', '+'),
    new CharTest('/\s/', '+'),
    new StringTest('hallo'),
    new CharTest('/\s/', '+'),
    new StringTest('welt'),
    new CharTest('/\s/', '+'),
    new CharTest('/x/', '+'),
    new CharTest('/U/', 4),
    new CharTest('/Z/'),
    new EofTest()
);

//fseek($handle, 0);
$result = $matcher->match($handle, $tests, true);

var_dump($result);

fseek($handle, 69);

$tests = array(
    new StringTest('hallo'),
    new CharTest('/\s/', '+'),
    new StringTest('welt')
);
$result = $matcher->match($handle, $tests, true);
var_dump($result);






$dataStream = 'data://text/plain;base64,' . base64_encode('welt ');
$handle = fopen($dataStream, 'rb');
$tests = array(
    new StringTest('welt'),
    new CharTest('/\s|$/')
);
$result = $matcher->match($handle, $tests, true);
var_dump($result);
