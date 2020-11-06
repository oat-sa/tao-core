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

declare(strict_types=1);

namespace oat\tao\model\search\index;

use Iterator;

/**
 * Class IndexDocument
 * @package oat\tao\model\search\index
 */
class IndexDocument
{
    /** @var string */
    protected $id;

    /** @var array */
    protected $body;

    /** @var array */
    protected $indexesProperties;

    /** @var Iterator|null */
    private $dynamicProperties;

    /** @var Iterator|null */
    private $accessProperties;

    /**
     * IndexDocument constructor.
     * @param string $id
     * @param array $body
     * @param array $indexesProperties
     * @param Iterator|null $dynamicProperties
     * @param Iterator|null $accessProperties
     * @throws \common_Exception
     */
    public function __construct(
        string $id,
        array $body,
        array $indexesProperties = [],
        Iterator $dynamicProperties = null,
        Iterator $accessProperties = null
    ) {
        $this->id = $id;

        if (!isset($body['type'])) {
            throw new \common_Exception('Body of indexDocument should contain type key');
        }
        $this->body = $body;
        $this->indexesProperties = $indexesProperties;
        $this->dynamicProperties = $dynamicProperties;
        $this->accessProperties = $accessProperties;
    }


    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Body of document
     *
     * $body['type'] = ['type1', 'type2'];
     * $body['label'] = 'label';
     * $body[$field'] = $value;
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * Array of IndexProperty
     *
     * @return array
     */
    public function getIndexProperties(): array
    {
        return $this->indexesProperties;
    }

    public function getDynamicProperties(): ?Iterator
    {
        return $this->dynamicProperties;
    }

    public function getAccessProperties(): ?Iterator
    {
        return $this->accessProperties;
    }
}
