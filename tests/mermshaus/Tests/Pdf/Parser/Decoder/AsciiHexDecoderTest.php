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

namespace mermshaus\Tests\Pdf\Parser\Decoder;

use mermshaus\Pdf\Parser\Decoder\AsciiHexDecoder;
use PHPUnit_Framework_TestCase;

class AsciiHexDecoderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestDecoder
     */
    public function testDecoder($input, $expectedResult)
    {
        $decoder = new AsciiHexDecoder();

        $this->assertEquals($expectedResult, $decoder->decode($input));
    }

    public function providerTestDecoder()
    {
        $tests = array();

        $tests[] = array(
            '>',
            ''
        );

        $tests[] = array(
            '    >',
            ''
        );

        $tests[] = array(
            '0>',
            "\0"
        );

        $tests[] = array(
            '2>',
            ' '
        );

        $tests[] = array(
            '48656c6c6f20576f726c6421>',
            'Hello World!'
        );

        $tests[] = array(
            '48656C6C6F20576F726C6421>',
            'Hello World!'
        );

        $tests[] = array(
            '48 65 6C 6C 6F 20 57 6F 72 6C 64 21>',
            'Hello World!'
        );

        $tests[] = array(
            '4 8 6 5 6 C 6 C 6 F 2 0 5
             7 6 F 7 2 6 C 6 4 2 1>',
            'Hello World!'
        );

        return $tests;
    }

    /**
     * @expectedException mermshaus\Pdf\Parser\PdfException
     * @dataProvider providerTestException
     */
    public function testException($input)
    {
        $decoder = new AsciiHexDecoder();
        $decoder->decode($input);
    }

    public function providerTestException()
    {
        return array(
            array(''),
            array('  '),
            array('ffffg>'),
            array('ffff>>'),
            array('ff%>')
        );
    }
}
