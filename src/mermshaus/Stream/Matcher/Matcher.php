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

namespace mermshaus\Stream\Matcher;

use mermshaus\Stream\Matcher\MatcherResult;

class Matcher
{
    public function match($handle, array $charTests, $returnMatches = true)
    {
        $matches = array();
        $success = true;
        $rolloverChar = '';
        $length = 0;

        foreach ($charTests as $test) {
            $ret = $test->run($handle, $rolloverChar, $returnMatches);

            if ($ret['found'] && $returnMatches) {
                $matches[] = $ret['buffer'];
            }

            $rolloverChar = $ret['rolloverChar'];
            $length += $ret['length'];

            if (!$ret['found']) {
                $success = false;
                break;
            }
        }

        return new MatcherResult($success, $length, $matches);
    }
}
