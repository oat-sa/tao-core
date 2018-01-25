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

/**
 * Class IndexDocument
 * @package oat\tao\model\search\index
 */
class IndexDocument
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $responseId;

    /** @var string */
    protected $type;

    /** @var array */
    protected $body;

    /**
     * IndexDocument constructor.
     * @param $id
     * @param $responseId
     * @param $type
     * @param $body
     */
    public function __construct(
        $id,
        $responseId,
        $type,
        $body
    ){
        $this->id = $id;
        $this->responseId = $responseId ? $responseId : $id;
        $this->type = $type;
        $this->body = $body;

    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getResponseId()
    {
        return $this->responseId;
    }

}
