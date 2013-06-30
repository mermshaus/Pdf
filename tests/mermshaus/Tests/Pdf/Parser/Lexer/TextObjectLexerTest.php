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

namespace mermshaus\Tests\Pdf\Parser\Lexer;

use mermshaus\Pdf\Parser\Lexer\TextObjectLexer;
use PHPUnit_Framework_TestCase;

/**
 *
 */
class TestObjectLexerTest extends PHPUnit_Framework_TestCase
{
    protected function pullTokenNames(array $tokens)
    {
        $tokenNames = array();

        foreach ($tokens as $token) {
            $tokenNames[] = $token['name'];
        }

        return $tokenNames;
    }

    /**
     * @dataProvider providerOperatorBrick
     */
    public function testOperatorBrick($expectedTokens, $source)
    {
        $lexer = new TextObjectLexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));
    }

    /**
     *
     * @return array
     */
    public function providerOperatorBrick()
    {
        return array(
            array(
                array(),
                ''
            ),
            array(
                array('numeric', 'white_space', 'numeric', 'white_space', 'operator'),
                '0 0 re'
            ),
            array(
                array('operator', 'white_space', 'numeric', 'white_space', 'numeric',
                    'white_space', 'operator', 'white_space', 'name', 'white_space', 'numeric', 'white_space',
                    'operator', 'array_start', 'string', 'numeric', 'string', 'numeric', 'string', 'numeric',
                    'string', 'numeric', 'string', 'numeric', 'string', 'numeric', 'string', 'numeric',
                    'string', 'numeric', 'string', 'numeric', 'string', 'numeric', 'string', 'array_end', 'operator', 'white_space', 'operator'),
                'BT
56.8 708.1 Td /F1 16.1 Tf[<01>1<02>1<03>-1<04>-2<05>1<06>-2<07>-2<08>8<09>-2<0A>1<05>]TJ
ET'
            ),
            array(
                array('operator', 'name', 'white_space', 'null', 'name', 'array_start', 'operator', 'array_end', 'operator',
                    'array_start', 'array_end', 'operator'),
                'T*/T* null/Tf[Tf]\'[]"'
            )
        );
    }

    /**
     * @expectedException mermshaus\Pdf\Parser\PdfException
     * @dataProvider providerException
     */
    public function testException($test)
    {
        $lexer = new TextObjectLexer();
        $lexer->tokenize($test);
    }

    public function providerException()
    {
        return array(
            array('unknown'),
            array('(error'),
            array('<error'),
        );
    }
}
