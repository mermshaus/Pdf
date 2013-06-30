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
use mermshaus\Pdf\Parser\DictionaryParser;
use mermshaus\Pdf\Parser\Lexer\Lexer;
use mermshaus\Pdf\Parser\ObjectParser;
use mermshaus\Pdf\Parser\Objects\PdfDictionary;
use mermshaus\Pdf\Parser\Objects\PdfReference;
use mermshaus\Pdf\Parser\Objects\PdfStreamObject;
use mermshaus\Pdf\Parser\PdfException;
use mermshaus\Pdf\Parser\PdfIndirectObject;
use mermshaus\Pdf\Parser\PdfStream;

/**
 *
 */
class ObjectRepository
{
    /**
     *
     * @var string
     */
    protected $source;

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
     * @var Lexer
     */
    protected $lexer;

    /**
     *
     * @var DictionaryParser
     */
    protected $dictionaryParser;

    /**
     *
     * @var ObjectParser
     */
    protected $objectParser;

    /**
     *
     * @param PdfStream $stream
     * @param array $crossReferenceTable
     */
    public function __construct(PdfStream $stream, CrossReferenceTable $crossReferenceTable)
    {
        $stream->push();
        $stream->rewind();
        $this->source              = $stream->getContents();
        $stream->pop();

        $this->stream = $stream;

        $this->crossReferenceTable = $crossReferenceTable;
        $this->lexer               = new Lexer();
        $this->dictionaryParser    = new DictionaryParser();
        $this->objectParser        = new ObjectParser();
    }

    /**
     *
     * @param int $id
     * @param int $revision
     */
    public function getObjectByIdAndRevision($id, $revision)
    {
        foreach ($this->crossReferenceTable as $t) {
            if ($t->getId() === $id && $t->getRevision() === $revision) {
                $object = $this->parseObjectFromOffset($t->getOffset());
                return $object;
            }
        }

        throw new PdfException('Could not load object');
    }

    /**
     *
     * @param int $offset
     * @return PdfIndirectObject
     * @throws PdfException
     */
    protected function parseObjectFromOffset($offset)
    {
        $matches = array();
        $object  = array();

        $this->stream->seek($offset);

        // assert "<x> <y> obj"
        if (false === $this->stream->consume(
                [['/[0-9]/','+'],['/\s/','+'],['/[0-9]/','+'],['/\s/','+'],'obj',['/[^a-z]/']],
                $matches
            )
        ) {
            throw new PdfException('No object at ' . $offset);
        }
        $this->stream->seek(-1, SEEK_CUR);

        $object['id']       = (int) $matches[0];
        $object['revision'] = (int) $matches[2];

        // Load PDF object
        list($endPos, $value) = $this->objectParser->getNextObjectFromString(
            $this->source,
            $this->stream->tell()
        );

        $this->stream->seek($endPos);

        // Assert key word
        if (false === $this->stream->consume(
                [['/\s/','*'],['/[a-z]/','+'],['/[^a-z]/']],
                $matches
            )
        ) {
            throw new PdfException('No key word found at ' . $this->stream->tell());
        }
        $this->stream->seek(-1, SEEK_CUR);

        $keyWord = $matches[1];

        switch ($keyWord) {
            case 'stream':
                if (!$value instanceof PdfDictionary) {
                    throw new PdfException('Value has to be a dictionary');
                }

                $this->stream->push();
                $length = $this->resolveRef($value->get('/Length'));
                $this->stream->pop();

                $chars = $this->stream->read(2);

                if ($chars === "\r\n") {
                    // nop
                } elseif ($chars[0] === "\n") {
                    $this->stream->seek(-1, SEEK_CUR);
                } else {
                    throw new PdfException('Expected \n or \r\n after stream key word');
                }

                $value = new PdfStreamObject($value, $this->stream->tell(), $length);

                $this->stream->seek($length, SEEK_CUR);

                // Assert "endstream"
                if (!$this->stream->consume([['/\s/','*'],'endstream'])) {
                    throw new PdfException('Expected endstream key word');
                }

                // Assert "endobj"
                if (!$this->stream->consume([['/\s/','*'],'endobj'])) {
                    throw new PdfException('Expected endobj key word');
                }
                break;

            case 'endobj':
                // nop
                break;

            default:
                throw new PdfException('Unexpected key word ' . $keyWord);
                break;
        }

        $object['value'] = $value;

        return new PdfIndirectObject($object['id'], $object['revision'], $object['value']);
    }

    /**
     * Resolves object reference if input is PdfReference
     *
     * @param mixed $value
     * @return mixed
     */
    public function resolveRef($value)
    {
        $newValue = $value;

        if ($value instanceof PdfReference) {
            $indirectObject = $this->getObjectByIdAndRevision(
                $value->getTargetId(),
                $value->getTargetRevision()
            );

            $newValue = $indirectObject->getValue();
        }

        return $newValue;
    }

    /**
     *
     * @return array
     */
    public function getAllObjects()
    {
        $objects = array();

        // Force loading of all objects
        foreach ($this->crossReferenceTable as $t) {
            if ($t->getFree() === 'n') {
                $id       = $t->getId();
                $revision = $t->getRevision();

                $objects[] = $this->getObjectByIdAndRevision($id, $revision);
            }
        }

        return $objects;
    }
}
