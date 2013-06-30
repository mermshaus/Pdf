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

namespace mermshaus\Pdf\Parser;

use mermshaus\Pdf\Parser\CrossReferenceTable;
use mermshaus\Pdf\Parser\CrossReferenceTableEntry;
use mermshaus\Pdf\Parser\ObjectParser;
use mermshaus\Pdf\Parser\Objects\PdfDictionary;
use mermshaus\Pdf\Parser\PdfException;
use mermshaus\Pdf\Parser\PdfStream;

/**
 *
 */
class CrossReferenceTableParser
{
    /**
     *
     * @var ObjectParser
     */
    protected $objectParser;

    /**
     *
     */
    public function __construct()
    {
        $this->objectParser = new ObjectParser();
    }

    /**
     *
     * @param PdfStream $stream
     * @return int
     * @throws PdfException
     */
    protected function getStartXref(PdfStream $stream)
    {
        $offset = (-1) * ($stream->getLength() - $stream->tell());

        $buffer = '';
        $char = $stream->read(1);

        while (is_numeric($char)) {
            $buffer .= $char;
            $offset--;

            $stream->seek($offset, SEEK_END);
            $char = $stream->read(1);
        }

        $offset--;
        $stream->seek($offset, SEEK_END);
        $s = $stream->read(2);

        if ($s === "\r\n") {
            $offset--;
        } elseif ($s[1] === "\r" || $s[1] === "\n") {
            // nop
        } else {
            throw new PdfException('PDF file seems to be invalid.');
        }

        $offset -= 8;
        $stream->seek($offset, SEEK_END);

        if ($stream->read(9) !== 'startxref') {
            throw new PdfException('PDF file seems to be invalid.');
        }

        $offset -= 2;
        $stream->seek($offset, SEEK_END);
        $s2 = $stream->read(2);

        if ($s2 === "\r\n") {
            // nop
        } elseif ($s2[1] === "\r" || $s2[1] === "\n") {
            // nop
        } else {
            throw new PdfException('PDF file seems to be invalid.');
        }

        return (int) strrev($buffer);
    }

    /**
     *
     * @param PdfStream $stream
     * @throws PdfException
     */
    protected function assertPdf(PdfStream $stream)
    {
        $offset = -2;

        $stream->seek($offset, SEEK_END);
        $s = $stream->read(2);

        if ($s === "\r\n") {
            $offset = -7;
        } elseif ($s[1] === "\r" || $s[1] === "\n") {
            $offset = -6;
        } else {
            $offset = -5;
        }

        $stream->seek($offset, SEEK_END);

        if ($stream->read(5) !== '%%EOF') {
            throw new PdfException('PDF file seems to be invalid.');
        }

        $offset -= 2;

        $stream->seek($offset, SEEK_END);
        $tmp = $stream->read(2);

        if ($tmp === "\r\n") {
            $offset--;
        } elseif (is_numeric($tmp[0]) && ($tmp[1]==="\r"||$tmp[1]==="\n")) {
            // nop
        } else {
            throw new PdfException('PDF file seems to be invalid.');
        }

        $stream->seek($offset, SEEK_END);
    }

    /**
     *
     * @param PdfStream $stream
     * @return CrossReferenceTable
     * @throws PdfException
     */
    protected function parseCrossReferenceTable(PdfStream $stream)
    {
        if (trim($stream->gets()) !== 'xref') {
            throw new PdfException('xref not found at startxref');
        }

        $objects = new CrossReferenceTable();

        $matches = array();

        $line = trim($stream->gets());

        if (0 === preg_match('/^([0-9]+) ([0-9]+)$/', $line, $matches)) {
            throw new PdfException('Invalid definition line in xref section');
        }

        $objectStartIndex = (int) $matches[1];
        $objectCount      = (int) $matches[2];

        for ($n = 0; $n < $objectCount; $n++) {
            $line = trim($stream->gets());

            $matches2 = array();

            if (0 === preg_match('/^([0-9]{10}) ([0-9]{5}) ([fn])$/', $line, $matches2)) {
                throw new PdfException('Invalid line in xref section');
            }

            $objects[] = new CrossReferenceTableEntry(
                $objectStartIndex + $n,
                (int) ($matches2[2]),
                (int) ($matches2[1]),
                $matches2[3]
            );
        }

        return $objects;
    }

    /**
     *
     * @param PdfStream $stream
     * @return PdfDictionary
     * @throws PdfException
     */
    protected function parseTrailer(PdfStream $stream)
    {
        if (trim($stream->gets()) !== 'trailer') {
            throw new PdfException('trailer expected');
        }

        list( , $object) = $this->objectParser->getNextObjectFromString($stream->getContents(), 0);

        if (!$object instanceof PdfDictionary) {
            throw new PdfException('No dictionary found in trailer');
        }

        return $object;
    }

    /**
     *
     * @param PdfStream $stream
     * @return array
     * @throws PdfException
     */
    public function parse(PdfStream $stream)
    {
        $this->assertPdf($stream);
        $startxref = $this->getStartXref($stream);

        $stream->seek($startxref);

        $crossReferenceTable = $this->parseCrossReferenceTable($stream);

        $trailer = $this->parseTrailer($stream);

        return array($crossReferenceTable, $trailer);
    }
}
