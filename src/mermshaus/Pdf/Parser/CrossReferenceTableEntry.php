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

namespace mermshaus\Pdf\Parser;

class CrossReferenceTableEntry
{
    protected $id;
    protected $revision;
    protected $offset;
    protected $free;

    /**
     *
     * @param int    $id
     * @param int    $revision
     * @param int    $offset
     * @param string $free
     */
    public function __construct($id, $revision, $offset, $free)
    {
        $this->id       = $id;
        $this->revision = $revision;
        $this->offset   = $offset;
        $this->free     = $free;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getFree()
    {
        return $this->free;
    }
}
