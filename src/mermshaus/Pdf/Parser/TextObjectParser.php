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

use mermshaus\Pdf\Parser\Lexer\TextObjectLexer;
use mermshaus\Pdf\Parser\PdfStream;

class TextObjectParser
{
    protected $data;

    protected $charMaps;

    protected function runOperator(array $operandBuffer, $operator)
    {
        $response = array(
            'type'    => 'default',
            'content' => ''
        );

        switch ($operator) {
            case 'Tf':
                if (count($operandBuffer) !== 2) {
                    throw new PdfException('Tf needs two operands');
                }
                $this->data['font'] = $operandBuffer[0];
                break;

            case 'TJ':
                if (count($operandBuffer) !== 1) {
                    throw new PdfException('TJ needs one operand');
                }
                $tokens = $operandBuffer[0];
                $text = '';
                foreach ($tokens as $token) {
                    if (substr($token, 0, 1) === '<') {
                        $matches = array();
                        if (0 === preg_match('/^<((?:[A-Fa-f0-9]{2})+)>$/', $token, $matches)) {
                            throw new PdfException('Invalid hex string');
                        }

                        $l = strlen($matches[1]);
                        $cm = $this->charMaps[$this->data['font']];
                        for ($i = 0; $i < $l; $i += 2) {
                            $byte = substr($matches[1], $i, 2);
                            $text .= $cm[base_convert($byte, 16, 10)];
                        }
                    }
                }

                $response['type']    = 'text';
                $response['content'] = $text . "\n";
                break;

            default:
                // Not implemented
                break;
        }

        return $response;
    }

    public function getText(PdfStream $stream, array $charMaps)
    {
        $this->charMaps = $charMaps;
        $this->data = array(
            'font' => ''
        );

        $textObjectLexer = new TextObjectLexer();

        $stream->rewind();
        $source = $stream->getContents();
        $offset = 0;
        $count  = strlen($source);

        $text   = '';

        // default, in_array
        $state = 'default';
        $operandBuffer = array();

        while ($offset < $count) {
            $token = $textObjectLexer->getNextTokenFromString($source, $offset);
            $tokenContent = substr($source, $offset, $token->newPos - $offset);

            switch ($token->tokenName) {
                case 'white_space':
                    // nop. Ignore white space
                    break;
                case 'operator':
                    if ($state === 'default') {
                        $response = $this->runOperator($operandBuffer, $tokenContent);

                        if ($response['type'] === 'text') {
                            $text .= $response['content'];
                        }

                        $operandBuffer = array();
                    } else {
                        throw new PdfException('Unexpected token ' . $token->tokenName);
                    }
                    break;
                case 'numeric':
                case 'name':
                case 'boolean':
                case 'string':
                case 'null':
                    if ($state === 'default') {
                        $operandBuffer[] = $tokenContent;
                    } elseif ($state === 'in_array') {
                        $operandBuffer[] = $tokenContent;
                    } else {
                        throw new PdfException('Unexpected token ' . $token->tokenName);
                    }
                    break;
                case 'array_start':
                    if ($state === 'default') {
                        if (count($operandBuffer) > 0) {
                            throw new PdfException('array_start but operand buffer not emtpy');
                        }

                        $state = 'in_array';
                    } else {
                        throw new PdfException('Unexpected token ' . $token->tokenName);
                    }
                    break;
                case 'array_end':
                    if ($state === 'in_array') {
                        $newArray = new Objects\PdfArray();
                        foreach ($operandBuffer as $entry) {
                            $newArray[] = $entry;
                        }
                        $operandBuffer = array();
                        $operandBuffer[] = $newArray;
                        $state = 'default';
                    } else {
                        throw new PdfException('Unexpected token ' . $token->tokenName);
                    }
                    break;
                default:
                    throw new PdfException('Unknown token ' . $token->tokenName);
                    break;
            }

            $offset = $token->newPos;
        }

        if ($state !== 'default') {
            throw new PdfException('default state expected, ' . $state . ' given');
        }

        if (count($operandBuffer) !== 0) {
            throw new PdfException('Empty operand buffer expected');
        }

        return $text;
    }
}
