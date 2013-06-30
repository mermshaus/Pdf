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

use mermshaus\Pdf\Parser\Lexer\Lexer;
use PHPUnit_Framework_TestCase;

/**
 *
 */
class LexerTest extends PHPUnit_Framework_TestCase
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
     * @dataProvider providerDictionaryBrick
     */
    public function testDictionaryBrick($expectedTokens, $source)
    {
        $lexer = new Lexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));
    }

    /**
     *
     * @return array
     */
    public function providerDictionaryBrick()
    {
        return array(
            array(
                array(),
                ''
            ),
            array(
                array('dictionary_start'),
                '<<'
            ),
            array(
                array('dictionary_start', 'white_space'),
                '<< '
            ),
            array(
                array('dictionary_end'),
                '>>'
            ),
            array(
                array('dictionary_end', 'white_space'),
                '>> '
            ),
            array(
                array('white_space', 'dictionary_start', 'white_space', 'dictionary_end', 'white_space'),
                ' << >> '
            ),
            array(
                array('dictionary_start', 'name'),
                '<</'
            )
        );
    }

    /**
     * @dataProvider providerBooleanBrick
     */
    public function testBooleanBrick($expectedTokens, $source)
    {
        $lexer = new Lexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));
    }

    /**
     *
     * @return array
     */
    public function providerBooleanBrick()
    {
        return array(
            array(
                array(),
                ''
            ),
            array(
                array('boolean'),
                'true'
            ),
            array(
                array('boolean', 'white_space'),
                'true '
            ),
            array(
                array('boolean'),
                'false'
            ),
            array(
                array('boolean', 'white_space'),
                'false '
            ),
            array(
                array('boolean', 'string'),
                'false()'
            ),
            array(
                array('boolean', 'array_start'),
                'false['
            ),
            array(
                array('boolean', 'array_end'),
                'false]'
            ),
            array(
                array('white_space', 'boolean', 'white_space', 'boolean', 'white_space'),
                ' true false '
            ),
            array(
                array('dictionary_start', 'boolean', 'dictionary_end', 'boolean', 'white_space'),
                '<<true>>false '
            ),
            array(
                array('name', 'white_space', 'boolean', 'name', 'white_space', 'boolean'),
                '/true true/false false'
            )
        );
    }

    /**
     * @dataProvider providerNameBrick
     */
    public function testNameBrick($expectedTokens, $source, $expectedNames)
    {
        $lexer = new Lexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));

        $names = array();

        foreach ($tokens as $token) {
            if ($token['name'] === 'name') {
                $names[] = $token['content'];
            }
        }

        $this->assertEquals($names, $expectedNames);
    }

    /**
     *
     * @return array
     */
    public function providerNameBrick()
    {
        return array(
            array(
                array(),
                '',
                array()
            ),
            array(
                array('name'),
                '/',
                array('/')
            ),
            array(
                array('name'),
                '/a',
                array('/a')
            ),
            array(
                array('name'),
                '/1',
                array('/1')
            ),
            array(
                array('name', 'name'),
                '//',
                array('/', '/')
            ),
            array(
                array('name', 'dictionary_start'),
                '/<<',
                array('/')
            ),
            array(
                array('string', 'name', 'string'),
                '(hello)/(world)',
                array('/')
            ),
            array(
                array('name', 'array_start'),
                '/[',
                array('/')
            ),
            array(
                array('name', 'array_end'),
                '/]',
                array('/')
            )
        );
    }

    /**
     * @dataProvider providerNumericBrick
     */
    public function testNumericBrick($expectedTokens, $source, $expectedNumerics)
    {
        $lexer = new Lexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));

        $numerics = array();

        foreach ($tokens as $token) {
            if ($token['name'] === 'numeric') {
                $numerics[] = $token['content'];
            }
        }

        $this->assertEquals($numerics, $expectedNumerics);
    }

    /**
     *
     * @return array
     */
    public function providerNumericBrick()
    {
        return array(
            array(
                array(),
                '',
                array()
            ),
            array(
                array('numeric'),
                '1',
                array('1')
            ),
            array(
                array('numeric'),
                '+1',
                array('+1')
            ),
            array(
                array('numeric'),
                '1.',
                array('1.')
            ),
            array(
                array('numeric'),
                '-.002',
                array('-.002')
            ),
            array(
                array('numeric', 'white_space'),
                '-1.3 ',
                array('-1.3')
            ),
            array(
                array('numeric', 'string'),
                '-1.3()',
                array('-1.3')
            ),
            array(
                array('numeric', 'dictionary_start'),
                '-1.3<<',
                array('-1.3')
            ),
            array(
                array('numeric', 'dictionary_end'),
                '-1.3>>',
                array('-1.3')
            ),
            array(
                array('numeric', 'name'),
                '-1.3/',
                array('-1.3')
            )
        );
    }

    /**
     * @dataProvider providerStringBrick
     */
    public function testStringBrick($expectedTokens, $source, $expectedNames)
    {
        $lexer = new Lexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));

        $names = array();

        foreach ($tokens as $token) {
            if ($token['name'] === 'string') {
                $names[] = $token['content'];
            }
        }

        $this->assertEquals($names, $expectedNames);
    }

    /**
     *
     * @return array
     */
    public function providerStringBrick()
    {
        return array(
            array(
                array(),
                '',
                array()
            ),
            array(
                array('string'),
                '()',
                array('()')
            ),
            array(
                array('string'),
                '<>',
                array('<>')
            ),
            array(
                array('string'),
                '(test)',
                array('(test)')
            ),
            array(
                array('string', 'string'),
                '(test)(hello)',
                array('(test)', '(hello)')
            ),
            array(
                array('string'),
                '((()test())())',
                array('((()test())())')
            ),
            array(
                array('white_space', 'string', 'dictionary_start', 'string', 'white_space'),
                ' (test)<<(hello) ',
                array('(test)', '(hello)')
            ),
            array(
                array('string', 'null'),
                '(test)null',
                array('(test)')
            ),
            array(
                array('string', 'string'),
                '(test)<EF>',
                array('(test)', '<EF>')
            ),
            array(
                array('string'),
                '<EF>',
                array('<EF>')
            ),
            array(
                array('dictionary_start', 'string', 'dictionary_end'),
                '<<<EF>>>',
                array('<EF>')
            )
        );
    }

    /**
     * @dataProvider providerNullBrick
     */
    public function testNullBrick($expectedTokens, $source)
    {
        $lexer = new Lexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));
    }

    /**
     *
     * @return array
     */
    public function providerNullBrick()
    {
        return array(
            array(
                array(),
                ''
            ),
            array(
                array('null'),
                'null'
            ),
            array(
                array('null', 'white_space'),
                'null '
            ),
            array(
                array('null', 'dictionary_start'),
                'null<<'
            ),
            array(
                array('null', 'array_end'),
                'null]'
            ),
            array(
                array('null', 'string'),
                'null()'
            ),
            array(
                array('null', 'name'),
                'null/'
            ),
            array(
                array('null', 'name', 'string', 'null'),
                'null/()null'
            )
        );
    }

    /**
     * @dataProvider providerReferenceBrick
     */
    public function testReferenceBrick($expectedTokens, $source)
    {
        $lexer = new Lexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));
    }

    /**
     *
     * @return array
     */
    public function providerReferenceBrick()
    {
        return array(
            array(
                array(),
                ''
            ),
            array(
                array('reference'),
                'R'
            ),
            array(
                array('reference', 'white_space'),
                'R '
            ),
            array(
                array('reference', 'dictionary_start'),
                'R<<'
            ),
            array(
                array('reference', 'array_end'),
                'R]'
            ),
            array(
                array('reference', 'string'),
                'R()'
            ),
            array(
                array('reference', 'name'),
                'R/'
            ),
            array(
                array('reference', 'name', 'string', 'reference'),
                'R/()R'
            )
        );
    }

    public function testBigTest()
    {
        $source = <<<'EOT'

<<false
   true/Name/Name2
123
43445
+17
-98
0
//Name<</Other#20Name
34.5
-3.62
+123.6
4.
-.002
0.0
/
false<<
/ /
>>

   >>

EOT;

        $expectedTokens = array(
            'white_space',
            'dictionary_start',
            'boolean',
            'white_space',
            'boolean',
            'name',
            'name',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'name',
            'name',
            'dictionary_start',
            'name',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'numeric',
            'white_space',
            'name',
            'white_space',
            'boolean',
            'dictionary_start',
            'white_space',
            'name',
            'white_space',
            'name',
            'white_space',
            'dictionary_end',
            'white_space',
            'dictionary_end',
            'white_space'
        );

        $lexer = new Lexer();

        $tokens = $lexer->tokenize($source);

        $this->assertEquals($expectedTokens, $this->pullTokenNames($tokens));
    }
}
