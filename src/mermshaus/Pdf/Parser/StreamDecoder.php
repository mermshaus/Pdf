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

use mermshaus\Pdf\Parser\Decoder\Ascii85Decoder;
use mermshaus\Pdf\Parser\Decoder\AsciiHexDecoder;
use mermshaus\Pdf\Parser\Decoder\FlateDecoder;
use mermshaus\Pdf\Parser\Decoder\RunLengthDecoder;
use mermshaus\Pdf\Parser\Objects\PdfArray;
use mermshaus\Pdf\Parser\Objects\PdfStreamObject;
use mermshaus\Pdf\Parser\PdfException;
use mermshaus\Pdf\Parser\PdfStream;

/**
 *
 */
class StreamDecoder
{
    /**
     *
     * @var PdfStream
     */
    protected $stream;

    /**
     *
     * @param PdfStream $stream
     */
    public function __construct(PdfStream $stream)
    {
        $this->stream = $stream;

        $this->decoders = array(
            '/FlateDecode'     => new FlateDecoder(),
            '/ASCII85Decode'   => new Ascii85Decoder(),
            '/ASCIIHexDecode'  => new AsciiHexDecoder(),
            '/RunLengthDecode' => new RunLengthDecoder()
        );
    }

    /**
     *
     * @param PdfStreamObject $streamObj
     * @return string
     * @throws PdfException
     */
    public function decodeStream(PdfStreamObject $streamObj)
    {
        $this->stream->push();
        $this->stream->seek($streamObj->getStartOffset());
        $stream = $this->stream->read($streamObj->getLength());
        $this->stream->pop();

        $options = $streamObj->getDictionary();
        $filters  = $options->get('/Filter');

        $ret = $stream;

        if (!is_array($filters)) {
            $filters = new PdfArray(array($filters));
        }

        foreach ($filters as $filter) {
            if (!array_key_exists($filter, $this->decoders)) {
                throw new PdfException(sprintf(
                    'No decoder found for %s.',
                    $filter
                ));
            }

            $ret = $this->decoders[$filter]->decode($ret);
        }

        return $ret;
    }
}
