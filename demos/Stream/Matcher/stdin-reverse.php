<?php
// Call:   $ echo -n "hello world" | php -f ./stdin-reverse.php
// Output: elloh\ndlrow\n

use mermshaus\Stream\Matcher\CharTest;
use mermshaus\Stream\Matcher\Matcher;

require __DIR__ . '/../../bootstrap.php';

$matcher = new Matcher();

$tests = array(
    new CharTest('/\S/', '+'),
    new CharTest('/ |$/')
);

$res = $matcher->match(STDIN, $tests, true);

while ($res->getSuccess()) {
    echo strrev($res->getMatches()[0]), "\n";

    $res = $matcher->match(STDIN, $tests, true);
}
