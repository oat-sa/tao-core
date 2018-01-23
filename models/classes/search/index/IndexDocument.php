<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */
namespace oat\tao\model\search\index;

class IndexDocument
{
    protected $id;
    protected $responseId;
    protected $type;
    protected $body;

    public function __construct($id, $responseId, $type, $body)
    {
        $this->id = $id;
        $this->responseId = $responseId ? $responseId : $id;
        $this->type = $type;
        $this->body = $body;

    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getResponseId()
    {
        return $this->responseId;
    }

}