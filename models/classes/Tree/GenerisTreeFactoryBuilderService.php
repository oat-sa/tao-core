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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\GenerisTreeFactory;

class GenerisTreeFactoryBuilderService extends ConfigurableService
{
    const SERVICE_ID = 'tao/GenerisTreeFactoryBuilder';

    const OPTION_SHOW_NO_LABEL_RESOURCES = 'showNoLabelResources';

    /** @var bool */
    private $showNoLabel;
    /**
     * @param boolean $showResources If `true` resources will be represented in thee. Otherwise only classes.
     * @param array $openNodes Class uris for which children array should be build as well
     * @param int $limit Limit of resources to be shown in one class
     * @param int $offset Offset for resources in one class
     * @param array $resourceUrisToShow All siblings of this resources will be loaded, independent of current limit
     * @param array $propertyFilter Additional property filters to apply to the tree
     * @param array $optionsFilter
     * @param array $extraProperties
     *
     * @return GenerisTreeFactory
     */
    public function build(
        $showResources,
        array $openNodes = [],
        $limit = 10,
        $offset = 0,
        array $resourceUrisToShow = [],
        array $propertyFilter = [],
        array $optionsFilter = [],
        array $extraProperties = []
    ) {

        return new GenerisTreeFactory(
            $showResources,
            $openNodes,
            $limit,
            $offset,
            $resourceUrisToShow,
            $propertyFilter,
            $optionsFilter,
            $extraProperties,
            $this->isShowNoLabel()
        );
    }

    /**
     * @return bool
     */
    public function isShowNoLabel()
    {
        if (is_null($this->showNoLabel)) {
            $this->showNoLabel = (bool) $this->getOption(static::OPTION_SHOW_NO_LABEL_RESOURCES);
        }
        return $this->showNoLabel;
    }

    /**
     * @param $flag
     * @return $this
     */
    public function setShowLabel($flag)
    {
        $this->showNoLabel = $flag;

        return $this;
    }
}