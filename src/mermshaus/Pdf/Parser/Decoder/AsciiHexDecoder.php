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

namespace mermshaus\Pdf\Parser\Decoder;

use mermshaus\Pdf\Parser\Decoder\AbstractDecoder;
use mermshaus\Pdf\Parser\PdfException;

class AsciiHexDecoder extends AbstractDecoder
{
    public function decode($text)
    {
        $output   = '';
        $count    = strlen($text) - 1;
        $buffer   = '';
        $pos      = 0;

        if ('>' !== substr($text, -1)) {
            throw new PdfException('Input doesn\'t end with ">"');
        }

        while ($pos < $count) {
            $char    = $text[$pos];
            $charOrd = ord($char);

            switch (true) {
                // Ignore white space
                case 0x00 === $charOrd:
                case 0x09 === $charOrd:
                case 0x0A === $charOrd:
                case 0x0C === $charOrd:
                case 0x0D === $charOrd:
                case 0x20 === $charOrd:
                    $pos++;
                    break;

                case $charOrd >= 48 && $charOrd <=  57:
                case $charOrd >= 65 && $charOrd <=  70:
                case $charOrd >= 97 && $charOrd <= 102:
                    if ($buffer === '') {
                        $buffer .= $char;
                    } else {
                        $output .= chr(hexdec($buffer . $char));
                        $buffer = '';
                    }

                    $pos++;
                    break;

                default:
                    throw new PdfException('Invalid byte in input');
                    break;
            }
        }

        if ('' !== $buffer) {
            $output .= chr(hexdec($buffer . '0'));
        }

        return $output;
    }
}
