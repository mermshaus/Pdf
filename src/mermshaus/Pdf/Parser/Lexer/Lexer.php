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

namespace mermshaus\Pdf\Parser\Lexer;

use mermshaus\Pdf\Parser\Lexer\LexerBrick\ArrayBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\BooleanBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\DictionaryBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\LexerBrickRequest;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\LexerBrickResponse;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\NameBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\NullBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\NumericBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\ReferenceBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\StringBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\WhiteSpaceBrick;
use mermshaus\Pdf\Parser\PdfException;
use mermshaus\Pdf\Parser\PdfStream;

/**
 *
 */
class Lexer
{
    /**
     *
     * @var array
     */
    protected $bricks = array();

    /**
     *
     */
    public function __construct()
    {
        $bricks = array();
        $bricks[] = new DictionaryBrick();
        $bricks[] = new WhiteSpaceBrick();
        $bricks[] = new NameBrick();
        $bricks[] = new BooleanBrick();
        $bricks[] = new NumericBrick();
        $bricks[] = new StringBrick();
        $bricks[] = new ArrayBrick();
        $bricks[] = new NullBrick();
        $bricks[] = new ReferenceBrick();

        $this->bricks = $bricks;
    }

    /**
     *
     * @param string $source
     * @return array
     * @throws PdfException
     */
    public function tokenize($source)
    {
        $pos = 0;
        $length = strlen($source);
        $tokens = array();

        $request = new LexerBrickRequest();

        $lastToken = '(start)';

        while ($pos < $length) {
            $request->source = $source;
            $request->pos    = $pos;
            $request->length = $length;

            $newPos = $pos;

            foreach ($this->bricks as $brick) {
                $response = $brick->test($request);

                if ($response->tokenName === 'do_not_want') {
                    continue;
                }

                $token = array(
                    'name'    => $response->tokenName,
                    'content' => substr($source, $pos, $response->newPos - $pos)
                );

                $tokens[] = $token;

                $newPos = $response->newPos;

                $lastToken = $token['name'];

                break;
            }

            if ($newPos === $pos) {
                throw new PdfException('Parse error after token ' . $lastToken . '. Char: "' . substr($request->source, $pos - 5, 10) . '"');
            }

            $pos = $newPos;
        }

        return $tokens;
    }

    /**
     *
     * @param string $source
     * @param int $offset
     * @return LexerBrickResponse
     * @throws PdfException
     */
    public function getNextTokenFromString($source, $offset = 0)
    {
        $response = null;
        $okay = false;

        $request = new LexerBrickRequest();

        $request->source = $source;
        $request->pos    = $offset;
        $request->length = strlen($source);

        foreach ($this->bricks as $brick) {
            $response = $brick->test($request);

            if ($response->tokenName === 'do_not_want') {
                continue;
            }

            $okay = true;

            break;
        }

        if (!$okay) {
            throw new PdfException('No token found in source');
        }

        return $response;
    }

    public function getNextObjectFromStream(PdfStream $stream)
    {

    }
}
