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

namespace mermshaus\Stream;

use mermshaus\Stream\Matcher\CharTest;
use mermshaus\Stream\Matcher\Matcher;
use mermshaus\Stream\Matcher\StringTest;

trait ConsumableTrait
{
    /**
     *
     * @var Matcher
     */
    protected $matcher;

    /**
     *
     * @param array $patterns
     * @param array $matches
     * @return bool
     */
    public function consume(array $patterns, array &$matches = null)
    {
        $this->push();

        $tests = array();

        foreach ($patterns as $pattern) {
            if (is_string($pattern)) {
                $tests[] = new StringTest($pattern);
            } elseif (is_array($pattern)) {
                if (count($pattern) === 2) {
                    $tests[] = new CharTest($pattern[0], $pattern[1]);
                } else {
                    $tests[] = new CharTest($pattern[0]);
                }
            }
        }

        $fetchMatches = ($matches !== null);

        $response = $this->matcher->match($this->handle, $tests, $fetchMatches);

        if ($fetchMatches && $response->getSuccess()) {
            $matches = $response->getMatches();
        }

        $this->pop();
        $this->seek($response->getMatchesLength(), SEEK_CUR);

        return $response->getSuccess();
    }
}
