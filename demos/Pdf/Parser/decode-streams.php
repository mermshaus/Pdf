<?php

use mermshaus\Pdf\Parser\Objects\PdfStreamObject;
use mermshaus\Pdf\Parser\PdfDocument;
use mermshaus\Pdf\Parser\PdfStream;

function convertInvalidUtf8CharacersToHex($s)
{
    // http://stackoverflow.com/questions/1401317/remove-non-utf8-characters-from-string
    $regex = <<<'END'
    /
        (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
        |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
        |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
        |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
        ){1,100}                        # ...one or more times
    | (.)                               # anything else
    /x
END;

    $first = preg_replace_callback($regex, function ($matches) {
        if (count($matches) === 1) {
            return $matches[0];
        }
        return '.';
        #return '{0x' . dechex(ord($matches[0])) . '} ';
    }, $s);

    // Also convert non-printable control characters
    $second = '';

    foreach (str_split($first) as $char) {
        switch (true) {
            case $char === "\n" || $char === "\r":
                $second .= $char;
                break;
            case ord($char) <= 31 || ord($char) === 127:
                #$second .= '{0x' . dechex(ord($char)) . '} ';
                $second .= '.';
                break;
            default:
                $second .= $char;
                break;
        }
    }

    return $second;
}

function e($s)
{
    /**
     * @todo There are still cases that will make htmlspecialchars fail without
     * ENT_SUBSTITUTE
     */
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

require __DIR__ . '/../../bootstrap.php';

$pdf = new PdfDocument();

$file = __DIR__ . '/../../../tests/mermshaus/Tests/Pdf/Parser/data/pdfs/writer-lorem.pdf';

$pdf->loadFromStream(new PdfStream(fopen($file, 'rb')));

$decodedStreams = array();

foreach ($pdf->getObjects() as $object) {
    $value = $object->getValue();

    if ($value instanceof PdfStreamObject) {
        $decodedStreams[] = array(
            'title' => 'Object (' . $object->getId() . ' ' . $object->getRevision() . ')',
            'content' => convertInvalidUtf8CharacersToHex($pdf->decodeStream($value))
        );
    }
}

header('Content-Type: text/html; charset=UTF-8');

?><!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="UTF-8" />
        <title>title</title>
        <style>
/*<![CDATA[*/
pre {
    word-break: break-all;
    word-wrap: break-word; /* Hack for some browsers */
}
/*]]>*/
        </style>
    </head>

    <body>

        <h1 id="toc">Table of contents</h1>

        <ol>
        <?php foreach ($decodedStreams as $item) : ?>
            <?php
            $tmp = $item['title'];
            $tmp2 = str_replace(' ', '_', $tmp);
            $href = '#' . preg_replace('/[^A-Za-z0-9_]/', '', $tmp2);
            ?>
            <li><a href="<?=e($href)?>"><?=e($item['title'])?></a></li>
        <?php endforeach; ?>
        </ol>


        <?php foreach ($decodedStreams as $item) : ?>
            <?php
            $tmp = $item['title'];
            $tmp2 = str_replace(' ', '_', $tmp);
            $id = preg_replace('/[^A-Za-z0-9_]/', '', $tmp2);
            ?>

            <h2 id="<?=e($id)?>"><?=e($item['title'])?></h2>
            <pre><?=e($item['content'])?></pre>
            <p><a href="#toc">top</a></p>

        <?php endforeach; ?>

    </body>

</html>
