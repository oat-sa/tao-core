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
 */

namespace oat\tao\model\Tree;


class GenerisTreeFactoryBuilderRequest
{
    /** @var bool */
    private $showResources;

    /** @var array */
    private $openNodes = [];

    /** @var int */
    private $limit = 10;

    /** @var int */
    private $offset = 0;

    /** @var array */
    private $resourceUrisToShow = [];

    /** @var array */
    private $propertyFilter = [];

    /** @var array */
    private $optionsFilter = [];

    /** @var array */
    private $extraProperties = [];

    /**
     * GenerisTreeFactoryBuilderRequest constructor.
     * @param bool $showResources
     * @param array $openNodes
     * @param int $limit
     * @param int $offset
     * @param array $resourceUrisToShow
     * @param array $propertyFilter
     * @param array $optionsFilter
     * @param array $extraProperties
     */
    public function __construct(
        $showResources,
        array $openNodes,
        $limit = 10,
        $offset = 0,
        array $resourceUrisToShow = [],
        array $propertyFilter = [],
        array $optionsFilter = [],
        array $extraProperties = []
    )
    {
        $this->showResources = $showResources;
        $this->openNodes = $openNodes;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->resourceUrisToShow = $resourceUrisToShow;
        $this->propertyFilter = $propertyFilter;
        $this->optionsFilter = $optionsFilter;
        $this->extraProperties = $extraProperties;
    }

    /**
     * @return bool
     */
    public function isShowResources()
    {
        return $this->showResources;
    }

    /**
     * @return array
     */
    public function getOpenNodes()
    {
        return $this->openNodes;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return array
     */
    public function getResourceUrisToShow()
    {
        return $this->resourceUrisToShow;
    }

    /**
     * @return array
     */
    public function getPropertyFilter()
    {
        return $this->propertyFilter;
    }

    /**
     * @return array
     */
    public function getOptionsFilter()
    {
        return $this->optionsFilter;
    }

    /**
     * @return array
     */
    public function getExtraProperties()
    {
        return $this->extraProperties;
    }
}
