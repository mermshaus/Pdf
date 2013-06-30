<?php

use mermshaus\Pdf\Parser\PdfDocument;
use mermshaus\Pdf\Parser\PdfStream;

require __DIR__ . '/../../bootstrap.php';

$pdf = new PdfDocument();

$file = __DIR__ . '/../../../tests/mermshaus/Tests/Pdf/Parser/data/pdfs/writer-lorem.pdf';

$pdf->loadFromStream(new PdfStream(fopen($file, 'rb')));

foreach ($pdf->getObjects() as $object) {
    echo '<p>' . $object->getId() . ':' . $object->getRevision() . '</p>';
    var_dump($object->getValue());
}
