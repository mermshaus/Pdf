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

use mermshaus\Stream\StreamException;

/**
 * Object wrapper for stream resources
 */
class Stream
{
    protected $handle;

    public function __construct($handle)
    {
        if (!is_resource($handle) || get_resource_type($handle) !== 'stream') {
            throw new StreamException('Invalid stream resource supplied');
        }

        $this->handle = $handle;
    }

    public function getContents()
    {
        return stream_get_contents($this->handle);
    }

    public function close()
    {
        fclose($this->handle);
    }

    public function read($bytes)
    {
        return fread($this->handle, $bytes);
    }

    public function gets()
    {
        return fgets($this->handle);
    }
}
