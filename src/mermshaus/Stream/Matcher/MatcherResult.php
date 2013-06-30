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

class MatcherResult
{
    protected $success;
    protected $matchesLength;
    protected $matches;

    public function __construct($success, $matchesLength = -1, array $matches = array())
    {
        $this->success = $success;
        $this->matchesLength = $matchesLength;
        $this->matches = $matches;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function getMatchesLength()
    {
        return $this->matchesLength;
    }

    public function getMatches()
    {
        return $this->matches;
    }
}
