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

class RunLengthDecoder extends AbstractDecoder
{
    public function decode($text)
    {
        $output         = '';
        $count          = strlen($text) - 1;
        $pos            = 0;
        $wantDescriptor = true;
        $descriptorOrd  = 0;

        if ("\x80" !== substr($text, -1)) {
            throw new PdfException('Invalid input. No end of input marker found');
        }

        while ($pos < $count) {
            if ($wantDescriptor) {
                $descriptorOrd = ord($text[$pos]);
                $pos++;
                $wantDescriptor = false;
            } else {
                if ($descriptorOrd <= 127) {
                    if ($pos + $descriptorOrd > $count) {
                        throw new PdfException('Want more bytes than are left in input');
                    }

                    $output .= substr($text, $pos, $descriptorOrd + 1);
                    $pos += $descriptorOrd + 1;
                } elseif ($descriptorOrd >= 129) {
                    $output .= str_repeat($text[$pos], 257 - $descriptorOrd);
                    $pos++;
                } else {
                    throw new PdfException('Invalid byte or unexpected end of input marker: ' . $descriptorOrd);
                }

                $wantDescriptor = true;
            }
        }

        if (!$wantDescriptor) {
            throw new PdfException('Encoded byte sequence expected before end of input');
        }

        return $output;
    }
}
