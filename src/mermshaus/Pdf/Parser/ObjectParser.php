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

use mermshaus\Pdf\Parser\DictionaryParser;
use mermshaus\Pdf\Parser\Lexer\Lexer;

/**
 *
 */
class ObjectParser
{
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
     */
    public function __construct()
    {
        $this->lexer            = new Lexer();
        $this->dictionaryParser = new DictionaryParser();
    }

    /**
     *
     * @param string $source
     * @param int $startPos
     * @return array
     */
    public function getNextObjectFromString($source, $startPos)
    {
        $nextToken = $this->lexer->getNextTokenFromString($source, $startPos);

        while ($nextToken->tokenName === 'white_space') {
            $startPos = $nextToken->newPos;
            $nextToken = $this->lexer->getNextTokenFromString($source, $startPos);
        }

        $value = '';
        $endPos = 0;

        switch ($nextToken->tokenName) {
            case 'numeric':
            case 'string':
            case 'boolean':
            case 'null':
                // value is $token content, expected are white space (optional) + endobj
                $value = substr($source, $startPos, $nextToken->newPos - $startPos);
                $endPos = $nextToken->newPos;
                break;
            case 'dictionary_start':
                // Dictionary. Find next balanced dictionary_end and use
                // DictionaryParser to parse the tokens into a PdfDictionary
                // object
                $dictionaryIndent = 1;

                $tmpPos  = $nextToken->newPos;
                $mytoken = $this->lexer->getNextTokenFromString($source, $tmpPos);

                while ($mytoken->tokenName !== 'dictionary_end' || $dictionaryIndent > 0) {
                    if ($mytoken->tokenName === 'dictionary_start') {
                        $dictionaryIndent++;
                    } elseif ($mytoken->tokenName === 'dictionary_end') {
                        $dictionaryIndent--;
                    }

                    if ($dictionaryIndent === 0 && $mytoken->tokenName === 'dictionary_end') {
                        break;
                    }

                    $tmpPos  = $mytoken->newPos;
                    $mytoken = $this->lexer->getNextTokenFromString($source, $tmpPos);
                }

                $value = $this->dictionaryParser->parse(substr($source, $startPos, $mytoken->newPos - $startPos));
                $endPos = $mytoken->newPos;
                break;
        }

        return array($endPos, $value);
    }
}
