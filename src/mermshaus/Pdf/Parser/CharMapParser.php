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

use mermshaus\Pdf\Parser\CharMap;
use mermshaus\Pdf\Parser\PdfStream;

/**
 *
 */
class CharMapParser
{
    protected function utf8($num)
    {
        $ret = '';

        if ($num <= 0x7F) {
            $ret = chr($num);
        } elseif ($num <= 0x7FF) {
            $ret = chr(($num >> 6) + 192)
                 . chr(($num & 63) + 128);
        } elseif ($num <= 0xFFFF) {
            $ret = chr(($num >> 12) + 224)
                 . chr((($num >> 6) & 63) + 128)
                 . chr(($num & 63) + 128);
        } elseif ($num <= 0x1FFFFF) {
            $ret = chr(($num >> 18) + 240)
                 . chr((($num >> 12) & 63) + 128)
                 . chr((($num >> 6) & 63) + 128)
                 . chr(($num & 63) + 128);
        } else {
            throw new PdfException('Could not resolve unicode code point ' . $num);
        }

        return $ret;
    }

    public function parse(PdfStream $stream)
    {
        $charmap = new CharMap();
        $matches = array();

        $stream->rewind();

        $line = trim($stream->gets());

        // want_begin, want_line_or_end, end
        $state = 'want_begin';

        while ($line !== false && $state !== 'end') {
            switch (true) {
                case (1 === preg_match('/^[0-9]+ beginbfchar$/', $line)):
                    if ($state === 'want_begin') {
                        $state = 'want_line_or_end';
                    } else {
                        throw new PdfException('Unexpected beginbfchar');
                    }
                    break;
                case (1 === preg_match('/^endbfchar$/', $line)):
                    if ($state === 'want_line_or_end') {
                        $state = 'end';
                    } else {
                        throw new PdfException('Unexpected endbfchar');
                    }
                    break;
                case (1 === preg_match('/^<([0-9A-Fa-f]+)> <([0-9A-Fa-f]+)>$/', $line, $matches)):
                    if ($state === 'want_begin') {
                        // nop, I guess
                    } elseif ($state === 'want_line_or_end') {
                        $key = base_convert($matches[1], 16, 10);
                        $value = $this->utf8(base_convert($matches[2], 16, 10));
                        $charmap[$key] = $value;
                    } else {
                        throw new PdfException('Unknown state');
                    }
                    break;
                default:
                    if ($state !== 'want_begin') {
                        throw new PdfException('Unkown state (default)');
                    }
                    break;
            }

            $line = trim($stream->gets());
        }

        if ($state !== 'end') {
            throw new PdfException('end not reached');
        }

        return $charmap;
    }
}
