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

use mermshaus\Stream\Stream;
use SplStack;

class SeekableStream extends Stream
{
    protected $lengthCache = -1;

    /**
     *
     * @var SplStack
     */
    protected $positionStack;

    public function __construct($handle)
    {
        parent::__construct($handle);

        $this->positionStack = new SplStack();
    }

    public function getLength($forceRefresh = false)
    {
        if ($forceRefresh) {
            $this->lengthCache = -1;
        }

        if ($this->lengthCache === -1) {
            $this->push();
            fseek($this->handle, 0, SEEK_END);
            $this->lengthCache = ftell($this->handle);
            $this->pop();
        }

        return $this->lengthCache;
    }

    public function push()
    {
        $this->positionStack->push(ftell($this->handle));
    }

    public function pop()
    {
        fseek($this->handle, $this->positionStack->pop());
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->handle, $offset, $whence);
    }

    public function tell()
    {
        return ftell($this->handle);
    }

    public function rewind()
    {
        rewind($this->handle);
    }
}
