<?php
/**
 * This file is part of mermshaus/Pdf.
 *
 * mermshaus/Pdf is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * mermshaus/Pdf is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with mermshaus/Pdf. If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2013 Marc Ermshaus <http://www.ermshaus.org/>
 */

namespace mermshaus\Tests\Pdf\Parser;

use mermshaus\Pdf\Parser\CrossReferenceTable;
use mermshaus\Pdf\Parser\CrossReferenceTableEntry;
use mermshaus\Pdf\Parser\ObjectRepository;
use mermshaus\Pdf\Parser\PdfStream;
use mermshaus\Pdf\Parser\StreamDecoder;
use PHPUnit_Framework_TestCase;

class StreamDecoderTest extends PHPUnit_Framework_TestCase
{
    public function testFilterCombinations()
    {
        $dataHex = '48 65 6c 6c 6f 20 57 6f 72 6c 64 21>';

        $dataHexFlate = gzcompress($dataHex);

        $streamObjectTemplate = <<<EOT
1 0 obj
<<
    /Length %s
    /Filter [ /FlateDecode /ASCIIHexDecode ]
>>
stream
%s
endstream
endobj
EOT;

        $streamObject = sprintf(
            $streamObjectTemplate,
            strlen($dataHexFlate),
            $dataHexFlate
        );

        $streamData = 'data://text/plain;base64,' . base64_encode($streamObject);

        $handle = fopen($streamData, 'rb');

        $pdfStream = new PdfStream($handle);

        $crossReferenceTable = new CrossReferenceTable();

        $crossReferenceTable[] = new CrossReferenceTableEntry(1, 0, 0, 'n');

        $objectRepository = new ObjectRepository($pdfStream, $crossReferenceTable);

        $pdfStreamObject = $objectRepository->getObjectByIdAndRevision(1, 0)->getValue();

        $streamDecoder = new StreamDecoder($pdfStream);

        $dataDecoded = $streamDecoder->decodeStream($pdfStreamObject);

        $this->assertEquals($dataDecoded, 'Hello World!');
    }
}
