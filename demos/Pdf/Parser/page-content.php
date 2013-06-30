<?php

use mermshaus\Pdf\Parser\CharMapParser;
use mermshaus\Pdf\Parser\PdfDocument;
use mermshaus\Pdf\Parser\PdfStream;
use mermshaus\Pdf\Parser\TextObjectParser;

require __DIR__ . '/../../bootstrap.php';

$pdf = new PdfDocument();

$rr = function ($value) use ($pdf) {
    return $pdf->resolveRef($value);
};

$stringToStream = function ($string) {
    $streamData = 'data://text/plain;base64,' . base64_encode($string);
    return fopen($streamData, 'rb');
};

$file = __DIR__ . '/../../../tests/mermshaus/Tests/Pdf/Parser/data/pdfs/writer-lorem.pdf';



$pdf->loadFromStream(new PdfStream(fopen($file, 'rb')));

$pageTree = $pdf->getPageTree();

foreach ($pageTree->get('/Kids') as $kid) {
    $page      = $rr($kid);
    $resources = $rr($page->get('/Resources'));
    $font      = $rr($resources->get('/Font'));

    $fontmap = array();
    $toUnicodeMap = array();

    foreach ($font->getKeys() as $key) {
        $fontmap[$key] = $rr($font->get($key));

        $tmp = $rr($font->get($key));

        if ($tmp->has('/ToUnicode')) {
            $toUnicodeMap[$key] = $rr($tmp->get('/ToUnicode'));
        }
    }

    $charMaps = array();

    foreach ($toUnicodeMap as $key => $data) {
        $charMapParser = new CharMapParser();
        $charMaps[$key] = $charMapParser->parse(new PdfStream($stringToStream($pdf->decodeStream($data))));
    }

    $contents = $rr($page->get('/Contents'));

    $textObjectParser = new TextObjectParser();

    echo '<pre>';

    echo $textObjectParser->getText(
        new PdfStream($stringToStream($pdf->decodeStream($contents))),
        $charMaps
    );

    echo '</pre>';
}

?><style>
/*<![CDATA[*/

body {
    background: #eee;
    padding: 10px 50px;
}

pre {
    padding: 4em 6em;
    font-family: sans-serif;
    border: 1px solid black;
    margin: 0 auto 50px auto;
    word-break: break-all;
    word-wrap: break-word; /* Hack for some browsers */
    width: 50em;
    line-height: 160%;
    background: white;
    color: black;
}

/*]]>*/
</style>
