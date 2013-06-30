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

use mermshaus\Pdf\Parser\Decoder\Ascii85Decoder;
use PHPUnit_Framework_TestCase;

class Ascii85DecoderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestDecoder
     */
    public function testDecoder($input, $expectedResult)
    {
        $decoder = new Ascii85Decoder();

        $this->assertEquals($expectedResult, $decoder->decode($input));
    }

    public function providerTestDecoder()
    {
        $tests = array();

        $tests[] = array(
            '~>',
            ''
        );

        $tests[] = array(
            'z~>',
            "\0\0\0\0"
        );

        $tests[] = array(
            '9jqo^BlbD-BleB1DJ+*+F(f,q/0JhKF<GL>Cj@.4Gp$d7F!,L7@<6@)/0JDEF<G%<+EV:2F!,
O<DJ+*.@<*K0@<6L(Df-\0Ec5e;DffZ(EZee.Bl.9pF"AGXBPCsi+DGm>@3BB/F*&OCAfu2/AKY
i(DIb:@FD,*)+C]U=@3BN#EcYf8ATD3s@q?d$AftVqCh[NqF<G:8+EV:.+Cf>-FD5W8ARlolDIa
l(DId<j@<?3r@:F%a+D58\'ATD4$Bl@l3De:,-DJs`8ARoFb/0JMK@qB4^F!,R<AKZ&-DfTqBG%G
>uD.RTpAKYo\'+CT/5+Cei#DII?(E,9)oF*2M7/c~>',
            'Man is distinguished, not only by his reason, but by this singular passion from other animals, which is a lust of the mind, that by a perseverance of delight in the continued and indefatigable generation of knowledge, exceeds the short vehemence of any carnal pleasure.'
        );

        return $tests;
    }

    /**
     * @expectedException mermshaus\Pdf\Parser\PdfException
     * @dataProvider providerTestException
     */
    public function testException($input)
    {
        $decoder = new Ascii85Decoder();
        $decoder->decode($input);
    }

    public function providerTestException()
    {
        return array(
            array(''),
            array('  '),
            array('9jqo^v~>'),
            array('z~>>'),
            array('z>')
        );
    }
}
