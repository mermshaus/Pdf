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

class StringBrick extends AbstractBrick
{
    public function test(LexerBrickRequest $request)
    {
        $response = new LexerBrickResponse();
        $response->tokenName = 'do_not_want';

        $c = substr($request->source, $request->pos, 1);

        // Check whether input does not start with string delimiter
        if ($c !== '(' && $c !== '<') {
            return $response;
        }

        $c1 = substr($request->source, $request->pos + 1, 1);

        // Check whether input is dictionary_start
        if ($c === '<' && $c1 === '<') {
            return $response;
        }

        if ($c === '(') {
            // Everything until next balanced closing round bracket ")" belongs
            // to this string
            $innerBracketCounter = 0;

            $pos = $request->pos + 1;
            $x = substr($request->source, $pos, 1);

            while ($x !== false && ($x !== ')' || $innerBracketCounter > 0)) {
                if ($x === '(') {
                    $innerBracketCounter++;
                } elseif ($x === ')') {
                    $innerBracketCounter--;
                }

                $pos++;
                $x = substr($request->source, $pos, 1);
            }

            if ($innerBracketCounter > 0 || $x !== ')') {
                return $response;
            }

            $response->tokenName = 'string';
            $response->newPos    = $pos + 1;
        } elseif ($c === '<') {
            // Everything until next closing angle bracket ">" belongs to this
            // string
            $pos = $request->pos + 1;
            $x = substr($request->source, $pos, 1);

            while ($x !== false && $x !== '>') {
                $pos++;
                $x = substr($request->source, $pos, 1);
            }

            if ($x !== '>') {
                return $response;
            }

            $response->tokenName = 'string';
            $response->newPos    = $pos + 1;
        }

        return $response;
    }
}
