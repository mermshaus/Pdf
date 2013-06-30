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

use mermshaus\Pdf\Parser\Lexer\Lexer;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\ArrayBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\BooleanBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\NameBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\NullBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\NumericBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\OperatorBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\StringBrick;
use mermshaus\Pdf\Parser\Lexer\LexerBrick\WhiteSpaceBrick;

class TextObjectLexer extends Lexer
{
    public function __construct()
    {
        $bricks = array();
        $bricks[] = new WhiteSpaceBrick();
        $bricks[] = new NameBrick();
        $bricks[] = new BooleanBrick();
        $bricks[] = new NumericBrick();
        $bricks[] = new StringBrick();
        $bricks[] = new ArrayBrick();
        $bricks[] = new NullBrick();
        $bricks[] = new OperatorBrick();

        $this->bricks = $bricks;
    }
}
