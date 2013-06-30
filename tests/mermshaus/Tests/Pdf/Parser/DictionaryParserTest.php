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

namespace mermshaus\Tests\Pdf\Parser;

use mermshaus\Pdf\Parser\DictionaryParser;
use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testDictParser()
    {
        $parser = new DictionaryParser();

        $dict = $parser->parse(file_get_contents(__DIR__ . '/data/dictionaries/dict-in-dict.dict'));

        $this->assertEquals(
            array('/Type', '/Subtype', '/Version', '/IntegerItem', '/StringItem', '/Subdictionary'),
            $dict->getKeys()
        );

        $subdict = $dict->get('/Subdictionary');

        $this->assertEquals(
            array('/Item1', '/Item2', '/LastItem', '/VeryLastItem', '/Filter'),
            $subdict->getKeys()
        );

        $array = $subdict->get('/Filter');

        $this->assertEquals(
            array('/ASCII85Decode', '/LZWDecode'),
            $array
        );
    }

    /**
     *
     */
    public function testDictCornerCasesParser()
    {
        $parser = new DictionaryParser();

        $dict = $parser->parse(file_get_contents(__DIR__ . '/data/dictionaries/corner-cases.dict'));

        $this->assertEquals(
            array('/foo', '/', '/1', '/2', '/3', '/4', '/5', '/6', '/8', '/9', '/10'),
            $dict->getKeys()
        );

        $subdict = $dict->get('/foo');

        $this->assertEquals(
            array(),
            $subdict->getKeys()
        );

        $array = $dict->get('/10');

        $this->assertEquals(true, is_array($array));
        $this->assertEquals(6, count($array));
    }
}
