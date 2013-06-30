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

namespace mermshaus\Pdf\Parser\Objects;

use mermshaus\Pdf\Parser\PdfException;

class PdfDictionary extends AbstractPdfObject
{
    protected $data;

    public function __construct()
    {
        $this->data = array();
    }

    public function add($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            throw new PdfException();
        }

        return $this->data[$key];
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function getKeys()
    {
        return array_keys($this->data);
    }

    public function clear()
    {
        $this->data = array();
    }

    public function remove($key)
    {
        if (!array_key_exists($key, $this->data)) {
            throw new PdfException();
        }

        unset($this->data[$key]);
    }
}
