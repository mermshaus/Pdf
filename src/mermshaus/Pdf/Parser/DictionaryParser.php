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

use mermshaus\Pdf\Parser\Lexer\Lexer;
use mermshaus\Pdf\Parser\Objects\PdfDictionary;
use mermshaus\Pdf\Parser\Objects\PdfReference;
use mermshaus\Pdf\Parser\PdfException;

/**
 *
 */
class DictionaryParser
{
    /**
     *
     * @var Lexer
     */
    protected $lexer;

    public function __construct()
    {
        $this->lexer = new Lexer();
    }

    /**
     *
     * @param string $s
     * @return array
     */
    protected function tokenize($s)
    {
        $tokens = $this->lexer->tokenize($s);

        // Remove white_space tokens
        $tokensReal = array();

        foreach ($tokens as $token) {
            if ($token['name'] !== 'white_space') {
                $tokensReal[] = $token;
            }
        }

        return $tokensReal;
    }

    /**
     *
     * @param array $tokens
     * @param int $indent
     * @return array
     * @throws PdfException
     */
    protected function parseArray(array $tokens, $indent = 0)
    {
        $array = array();
        $tokenCount = count($tokens);
        // start, stop, want_value
        $state = 'start';
        $pos = 0;

        while ($pos < $tokenCount) {
            $token = $tokens[$pos];

            switch ($token['name']) {
                case 'array_start':
                    if ($state === 'start') {
                        $state = 'want_value';
                        $pos++;
                    }  elseif ($state === 'want_value') {
                        // Find next balanced array_end and call parseArray()
                        $arrayIndent = 0;
                        $testPos = $pos + 1;

                        while (
                            $testPos <= $tokenCount
                            && (
                                $tokens[$testPos]['name'] !== 'array_end'
                                || $arrayIndent > 0
                            )
                        ) {
                            if ($tokens[$testPos]['name'] === 'array_start') {
                                $arrayIndent++;
                            } elseif ($tokens[$testPos]['name'] === 'array_end') {
                                $arrayIndent--;
                            }

                            $testPos++;
                        }

                        if ($testPos > $tokenCount) {
                            throw new PdfException('Unbalanced arrays');
                        }

                        $array[] = $this->parseArray(array_slice($tokens, $pos, $testPos - $pos + 1), $indent + 1);
                        $state = 'want_value';
                        $pos = $testPos + 1;
                    } else {
                        throw new PdfException('Unexpected <' . $token['name'] . '> in <array> at ' . $pos);
                    }
                    break;

                case 'array_end':
                    if ($state === 'want_value') {
                        $state = 'stop';
                        $pos++;
                    } else {
                        throw new PdfException('Unexpected <' . $token['name'] . '> in <array> at ' . $pos);
                    }
                    break;

                case 'dictionary_start':
                    if ($state === 'want_value') {
                        // Sub-dictionary. Find next balanced dictionary_end
                        // and call this method recursively
                        $dictionaryIndent = 0;
                        $testPos = $pos + 1;

                        while (
                            $testPos <= $tokenCount
                            && (
                                $tokens[$testPos]['name'] !== 'dictionary_end'
                                || $dictionaryIndent > 0
                            )
                        ) {
                            if ($tokens[$testPos]['name'] === 'dictonary_start') {
                                $dictionaryIndent++;
                            } elseif ($tokens[$testPos]['name'] === 'dictionary_end') {
                                $dictionaryIndent--;
                            }

                            $testPos++;
                        }

                        if ($testPos > $tokenCount) {
                            throw new PdfException('Unbalanced dictionaries');
                        }

                        $array[] = $this->parseDictionary(array_slice($tokens, $pos, $testPos - $pos + 1), $indent + 1);
                        $state = 'want_value';
                        $pos = $testPos + 1;
                    } else {
                        throw new PdfException('Unexpected <' . $token['name'] . '> in <array> at ' . $pos);
                    }
                    break;

                case 'numeric':
                    if ($state === 'want_value') {
                        if ($pos + 2 < $tokenCount) {
                            if ($tokens[$pos + 1]['name'] === 'numeric' && $tokens[$pos + 2]['name'] === 'reference') {
                                $array[] = new PdfReference((int) $tokens[$pos]['content'], (int) $tokens[$pos + 1]['content']);
                                $state = 'want_value';
                                $pos += 3;
                            } else {
                                $array[] = $token['content'];
                                $state = 'want_value';
                                $pos++;
                            }
                        } else {
                            $array[] = $token['content'];
                            $state = 'want_value';
                            $pos++;
                        }
                    } else {
                        throw new PdfException('Unexpected <' . $token['name'] . '> at ' . $pos);
                    }
                    break;

                case 'name':
                case 'string':
                case 'boolean':
                case 'null':
                    if ($state === 'want_value') {
                        $array[] = $token['content'];
                        $pos++;
                    } else {
                        throw new PdfException('Unexpected <' . $token['name'] . '> in <array> at ' . $pos);
                    }
                    break;

                default:
                    throw new PdfException('Unexpected <' . $token['name'] . '> in <array> at ' . $pos);
                    break;
            }
        }

        if ($state !== 'stop') {
            throw new PdfException('Unexpected end of input in array');
        }

        return $array;
    }

    /**
     *
     * @param array $tokens
     * @param int $indent
     * @return PdfDictionary
     * @throws PdfException
     */
    protected function parseDictionary(array $tokens, $indent = 0)
    {
        $dict = new PdfDictionary();
        $tokenCount = count($tokens);
        // start, stop, want_name_or_end, want_value
        $state = 'start';
        $pos = 0;
        $lastName = '';

        while ($pos < $tokenCount) {
            $token = $tokens[$pos];

            // Debug output
            //echo str_repeat(' ', $indent * 2) . $pos . ' ' .$token['name'] . "\n";

            switch ($token['name']) {
                case 'dictionary_start':
                    if ($state === 'start') {
                        $state = 'want_name_or_end';
                        $pos++;
                    } elseif ($state === 'want_name_or_end') {
                        throw new PdfException('<name> or <dictionary_end> expected, <dictionary_start> given');
                    } elseif ($state === 'want_value') {
                        // Sub-dictionary. Find next balanced dictionary_end
                        // and call this method recursively
                        $dictionaryIndent = 0;
                        $testPos = $pos + 1;

                        while (
                            $testPos <= $tokenCount
                            && (
                                $tokens[$testPos]['name'] !== 'dictionary_end'
                                || $dictionaryIndent > 0
                            )
                        ) {
                            if ($tokens[$testPos]['name'] === 'dictonary_start') {
                                $dictionaryIndent++;
                            } elseif ($tokens[$testPos]['name'] === 'dictionary_end') {
                                $dictionaryIndent--;
                            }

                            $testPos++;
                        }

                        if ($testPos > $tokenCount) {
                            throw new PdfException('Unbalanced dictionaries');
                        }

                        $dict->add($lastName, $this->parseDictionary(array_slice($tokens, $pos, $testPos - $pos + 1), $indent + 1));
                        $lastName = '';
                        $state = 'want_name_or_end';
                        $pos = $testPos + 1;
                    }
                    break;

                case 'dictionary_end':
                    if ($state === 'want_name_or_end') {
                        $state = 'stop';
                        $pos++;
                    } else {
                        throw new PdfException('Unexpected <dictionary_end> at ' . $pos);
                    }
                    break;

                case 'array_start':
                    if ($state === 'want_value') {
                        // Find next balanced array_end and call parseArray()
                        $arrayIndent = 0;
                        $testPos = $pos + 1;

                        while (
                            $testPos <= $tokenCount
                            && (
                                $tokens[$testPos]['name'] !== 'array_end'
                                || $arrayIndent > 0
                            )
                        ) {
                            if ($tokens[$testPos]['name'] === 'array_start') {
                                $arrayIndent++;
                            } elseif ($tokens[$testPos]['name'] === 'array_end') {
                                $arrayIndent--;
                            }

                            $testPos++;
                        }

                        if ($testPos > $tokenCount) {
                            throw new PdfException('Unbalanced arrays');
                        }

                        $dict->add($lastName, $this->parseArray(array_slice($tokens, $pos, $testPos - $pos + 1), $indent + 1));
                        $lastName = '';
                        $state = 'want_name_or_end';
                        $pos = $testPos + 1;
                    } else {
                        throw new PdfException('Unexpected <' . $token['name'] . '> at ' . $pos);
                    }
                    break;

                case 'name':
                    if ($state === 'want_name_or_end') {
                        $lastName = $token['content'];
                        $state = 'want_value';
                        $pos++;
                    } elseif ($state === 'want_value') {
                        $dict->add($lastName, $token['content']);
                        $lastName = '';
                        $state = 'want_name_or_end';
                        $pos++;
                    } else {
                        throw new PdfException('Unexpected <name> at ' . $pos);
                    }
                    break;

                case 'string':
                case 'boolean':
                case 'null':
                    if ($state === 'want_value') {
                        $dict->add($lastName, $token['content']);
                        $lastName = '';
                        $state = 'want_name_or_end';
                        $pos++;
                    } else {
                        throw new PdfException('Unexpected <' . $token['name'] . '> at ' . $pos);
                    }
                    break;

                case 'numeric':
                    if ($state === 'want_value') {
                        if ($pos + 2 < $tokenCount) {
                            if ($tokens[$pos + 1]['name'] === 'numeric' && $tokens[$pos + 2]['name'] === 'reference') {
                                $dict->add($lastName, new PdfReference((int) $tokens[$pos]['content'], (int) $tokens[$pos + 1]['content']));
                                $lastName = '';
                                $state = 'want_name_or_end';
                                $pos += 3;
                            } else {
                                $dict->add($lastName, $token['content']);
                                $lastName = '';
                                $state = 'want_name_or_end';
                                $pos++;
                            }
                        } else {
                            $dict->add($lastName, $token['content']);
                            $lastName = '';
                            $state = 'want_name_or_end';
                            $pos++;
                        }
                    } else {
                        throw new PdfException('Unexpected <' . $token['name'] . '> at ' . $pos);
                    }
                    break;

                case 'reference':
                    // References are handled by numeric
                    throw new PdfException('Unexpected <' . $token['name'] . '>');
                    break;

                default:
                    throw new PdfException('Unexpected <' . $token['name'] . '>. This is a serious bug');
                    break;
            }
        }

        if ($state !== 'stop') {
            throw new PdfException('Unexpected end of input');
        }

        return $dict;
    }

    /**
     *
     * @param string $s
     * @return PdfDictionary
     * @throws PdfException
     */
    public function parse($s)
    {
        $tokens = $this->tokenize($s);

        if (count($tokens) < 2) {
            throw new PdfException('Invalid dictionary');
        }

        if (
            $tokens[0]['name'] !== 'dictionary_start'
            && $tokens[count($tokens) - 1]['name'] !== 'dictionary_end'
        ) {
            throw new PdfException('Invalid dictionary');
        }

        $pdfDictionary = $this->parseDictionary($tokens);

        return $pdfDictionary;
    }
}
