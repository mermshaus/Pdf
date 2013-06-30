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

namespace mermshaus\Pdf\Parser\Objects;

use mermshaus\Pdf\Parser\Objects\AbstractPdfObject;
use mermshaus\Pdf\Parser\Objects\PdfDictionary;

/**
 *
 */
class PdfStreamObject extends AbstractPdfObject
{
    /**
     *
     * @var PdfDictionary
     */
    protected $dictionary;

    protected $startOffset;

    protected $length;

    /**
     *
     * @param PdfDictionary $dictionary
     * @param string $streamData
     */
    public function __construct(PdfDictionary $dictionary, $startOffset, $length)
    {
        $this->dictionary = $dictionary;
        $this->startOffset = $startOffset;
        $this->length = $length;
    }

    /**
     *
     * @return PdfDictionary
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    public function getStartOffset()
    {
        return $this->startOffset;
    }

    public function getLength()
    {
        return $this->length;
    }
}
