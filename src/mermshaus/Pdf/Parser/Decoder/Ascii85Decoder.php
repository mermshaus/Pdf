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

/**
 *
 */
class Ascii85Decoder extends AbstractDecoder
{
    /**
     *
     * @param string $text
     * @return string
     */
    public function decode($text)
    {
        $output = '';
        $count  = strlen($text) - 2;
        $state  = 0;
        $pos    = 0;
        $buffer = array();

        if ('~>' !== substr($text, -2)) {
            throw new PdfException('Input doesn\'t end with "~>"');
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

                case 'z' === $char:
                    if ($state === 0) {
                        $output .= str_repeat("\0", 4);
                        $pos++;
                    } else {
                        throw new PdfException('Unexpected byte "z" in input');
                    }
                    break;

                // Out of range
                case $charOrd >= 33 && $charOrd <= 117:
                    $buffer[$state] = $charOrd - 33;

                    if (4 === $state) {
                        $sum = 0;

                        for ($i = 0; $i <= 4; $i++) {
                            $sum *= 85;
                            $sum += $buffer[$i];
                        }

                        $output .= chr($sum >> 24 & 0xFF)
                                 . chr($sum >> 16 & 0xFF)
                                 . chr($sum >>  8 & 0xFF)
                                 . chr($sum       & 0xFF);

                        $state = 0;
                    } else {
                        $state++;
                    }

                    $pos++;
                    break;

                default:
                    throw new PdfException('Invalid byte in input');
                    break;
            }
        }

        if ($state === 1) {
            throw new PdfException();
        }

        $sum = 0;

        for ($i = 0; $i < $state; $i++) {
            $offset = ($i === $state - 1) ? 1 : 0;
            $sum += ($buffer[$i] + $offset) * pow(85, 4 - $i);
        }

        for ($i = 0; $i < $state - 1; $i++) {
            $output .= chr($sum >> ((3 - $i) * 8) & 0xFF);
        }

        return $output;
    }
}
