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

class DictionaryBrick extends AbstractBrick
{
    public function test(LexerBrickRequest $request)
    {
        $response = new LexerBrickResponse();
        $response->tokenName = 'do_not_want';

        $test = substr($request->source, $request->pos, 2);

        if ($test === '<<') {
            $response->tokenName = 'dictionary_start';
            $response->newPos = $request->pos + 2;
        } elseif ($test === '>>') {
            $response->tokenName = 'dictionary_end';
            $response->newPos = $request->pos + 2;
        }

        return $response;
    }
}
