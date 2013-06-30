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

use mermshaus\Pdf\Parser\Decoder\RunLengthDecoder;
use PHPUnit_Framework_TestCase;

class RunLengthDecoderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestDecoder
     */
    public function testDecoder($input, $expectedResult)
    {
        $decoder = new RunLengthDecoder();

        $this->assertEquals($expectedResult, $decoder->decode($input));
    }

    public function providerTestDecoder()
    {
        $tests = array();

        $tests[] = array(
            "\x80",
            ''
        );

        $tests[] = array(
            "\x04Hello\x80",
            'Hello'
        );

        $tests[] = array(
            "\xFCx\x80",
            'xxxxx'
        );

        $tests[] = array(
            "\x81x\x80",
            str_repeat('x', 128)
        );

        $tests[] = array(
            "\xFCx\x04Hello\x80",
            'xxxxxHello'
        );

        return $tests;
    }

    /**
     * @expectedException mermshaus\Pdf\Parser\PdfException
     * @dataProvider providerTestException
     */
    public function testException($input)
    {
        $decoder = new RunLengthDecoder();
        $decoder->decode($input);
    }

    public function providerTestException()
    {
        return array(
            array(''),
            array('  '),
            array("\x80\x80"),
            array("\xFCx\x04Hello"),
            array("\xFCx\x04\x80"),
            array("\xFC\x80")
        );
    }
}
