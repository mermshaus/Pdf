<?php

use mermshaus\Pdf\Parser\PdfStream;

require __DIR__ . '/../../bootstrap.php';

$file = __DIR__ . '/../../../tests/mermshaus/Tests/Pdf/Parser/data/pdfs/writer-lorem.pdf';

$stream = new PdfStream(fopen($file, 'rb'));
