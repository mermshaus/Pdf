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

namespace mermshaus\Pdf\Parser\Lexer\LexerBrick;

/**
 *
 */
class LexerBrickRequest
{
    /**
     *
     * @var string
     */
    public $source;

    /**
     *
     * @var int
     */
    public $pos;

    /**
     *
     * @todo What exactly does $length specify? strlen($source) or range in
     *       $source to analyze (from $offset to $offset + $length)?
     * @var int
     */
    public $length;
}
