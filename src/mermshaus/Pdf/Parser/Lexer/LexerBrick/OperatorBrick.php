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

namespace mermshaus\Pdf\Parser\Lexer\LexerBrick;

/**
 *
 */
class OperatorBrick extends AbstractBrick
{
    protected $operators = array(
        'b',
        'B',
        'b*',
        'B*',
        'BDC',
        'BI',
        'BMC',
        'BT',
        'BX',
        'c',
        'cm',
        'CS',
        'cs',
        'd',
        'd0',
        'd1',
        'Do',
        'DP',
        'EI',
        'EMC',
        'ET',
        'EX',
        'f',
        'F',
        'f*',
        'G',
        'g',
        'gs',
        'h',
        'i',
        'ID',
        'j',
        'J',
        'K',
        'k',
        'l',
        'm',
        'M',
        'MP',
        'n',
        'q',
        'Q',
        're',
        'RG',
        'rg',
        'ri',
        's',
        'S',
        'SC',
        'sc',
        'SCN',
        'scn',
        'sh',
        'T*',
        'Tc',
        'Td',
        'TD',
        'Tf',
        'Tj',
        'TJ',
        'TL',
        'Tm',
        'Tr',
        'Ts',
        'Tw',
        'Tz',
        'v',
        'w',
        'W',
        'W*',
        'y',
        '\'',
        '"'
    );

    public function test(LexerBrickRequest $request)
    {
        $response = new LexerBrickResponse();
        $response->tokenName = 'do_not_want';

        if (1 === preg_match('/[A-Za-z\'"]/', substr($request->source, $request->pos, 1))) {
            $pos = $request->pos + 1;

            $c = substr($request->source, $pos, 1);

            while ($c !== false && 0 === preg_match($this->delimiterRegex, $c)) {
                $pos++;
                $c = substr($request->source, $pos, 1);
            }

            $test = substr($request->source, $request->pos, $pos - $request->pos);

            if (in_array($test, $this->operators)) {
                $response->tokenName = 'operator';
                $response->newPos = $pos;
            }
        }

        return $response;
    }
}
