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
use mermshaus\Pdf\Parser\CrossReferenceTableParser;
use mermshaus\Pdf\Parser\ObjectRepository;
use mermshaus\Pdf\Parser\Objects\PdfDictionary;
use mermshaus\Pdf\Parser\Objects\PdfStreamObject;
use mermshaus\Pdf\Parser\PdfStream;
use mermshaus\Pdf\Parser\StreamDecoder;

/**
 *
 */
class PdfDocument
{
    /**
     *
     * @var PdfStream
     */
    protected $stream;

    /**
     *
     * @var CrossReferenceTable
     */
    protected $crossReferenceTable;

    /**
     *
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     *
     * @var StreamDecoder
     */
    protected $streamDecoder;

    /**
     *
     * @var CrossReferenceTableParser
     */
    protected $crossReferenceTableParser;

    /**
     *
     * @var PdfDictionary
     */
    protected $trailer;

    /**
     *
     */
    public function __construct()
    {
        $this->crossReferenceTableParser = new CrossReferenceTableParser();

        $this->reset();
    }

    /**
     *
     */
    protected function reset()
    {
        $this->stream = null;
        $this->objectRepository = null;
    }

    /**
     *
     * @param string $stream
     */
    public function loadFromStream(PdfStream $stream)
    {
        $this->reset();

        $stream->push();
        list($this->crossReferenceTable, $this->trailer) = $this->crossReferenceTableParser->parse($stream);
        $stream->pop();

        $stream->push();
        $this->objectRepository    = new ObjectRepository($stream, $this->crossReferenceTable);
        $stream->pop();

        $this->stream              = $stream;


        $this->streamDecoder       = new StreamDecoder($this->stream);

//        if ($this->trailer->has('/Info')) {
//            $ref = $this->trailer->get('/Info');
//            $infoDict = $this->objectRepository->getObjectByIdAndRevision($ref->getTargetId(), $ref->getTargetRevision())->getValue();
//
//            var_dump($infoDict);
//        }
    }

    /**
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->objectRepository->getAllObjects();
    }

    /**
     *
     * @param PdfStreamObject $streamObj
     * @return type
     */
    public function decodeStream(PdfStreamObject $streamObj)
    {
        return $this->streamDecoder->decodeStream($streamObj);
    }

    public function resolveRef($value)
    {
        return $this->objectRepository->resolveRef($value);
    }

    /**
     *
     * @return PdfDictionary
     */
    public function getDocumentCatalog()
    {
        return $this->resolveRef($this->trailer->get('/Root'));
    }

    public function getPageTree()
    {
        return $this->resolveRef($this->getDocumentCatalog()->get('/Pages'));
    }
}
