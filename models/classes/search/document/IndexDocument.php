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
 *
 *
 */
namespace oat\tao\model\search\document;

/**
 * Class IndexDocument
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @package oat\tao\model\search\document
 */
class IndexDocument implements Document
{
    protected $id;
    protected $body;
    protected $index;
    protected $type;
    protected $provider;
    protected $responseId;

    /**
     * IndexDocument constructor.
     * @param       $id
     * @param       $responseId
     * @param       $index
     * @param       $provider
     * @param       $rootClass
     * @param       $type
     * @param array $body
     */
    public function __construct($id, $responseId, $index, $provider, $rootClass, $type = '', $body = [])
    {
        $this->id = $id;
        $this->responseId = $responseId;
        $this->provider = $provider;
        $this->index = $index;
        $this->type = $type;
        $body['provider'] = $provider;
        $body['rootClass'] = $rootClass;
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getResponseIdentifier()
    {
        return $this->responseId;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }
}
