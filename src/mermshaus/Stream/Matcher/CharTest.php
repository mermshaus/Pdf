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

use mermshaus\Stream\Matcher\MatcherTestInterface;

class CharTest implements MatcherTestInterface
{
    protected $range = '/./';
    protected $quantifier = 1;

    /**
     *
     * @param string $range
     * @param string|int $quantifier All quantifiers are greedy (values: numeric > 0, '+' or '*')
     */
    public function __construct($range, $quantifier = 1)
    {
        $this->range = $range;
        $this->quantifier = $quantifier;
    }

    public function getRange()
    {
        return $this->range;
    }

    public function getQuantifier()
    {
        return $this->quantifier;
    }

    public function run($handle, $rolloverChar, $returnMatches = false)
    {
        $buffer = '';
        $amount = 0;
        $quantifier = $this->getQuantifier();
        $found = false;
        $error = false;
        $newRolloverChar = '';

        if ($rolloverChar === '') {
            $char = fgetc($handle);
        } else {
            $char = $rolloverChar;
        }

        while ($found === false && $error === false) {
            if ($char === false) {
                $char = '';
            }

            if (1 === preg_match($this->getRange(), $char)) {
                $amount++;

                if ($returnMatches) {
                    $buffer .= $char;
                }

                if (is_int($quantifier)) {
                    if ($amount === $quantifier) {
                        $found = true;
                    }
                }
            } else {
                $error = true;

                if ('*' === $quantifier) {
                    $error = false;
                    $found = true;
                    $newRolloverChar = $char;
                } elseif ('+' === $quantifier) {
                    if ($amount > 0) {
                        $error = false;
                        $found = true;
                        $newRolloverChar = $char;
                    }
                }
            }

            if (!$found && !$error) {
                $char = fgetc($handle);
            }
        }

        return array(
            'found'        => $found,
            'buffer'       => $buffer,
            'length'       => $amount,
            'rolloverChar' => $newRolloverChar
        );
    }
}
