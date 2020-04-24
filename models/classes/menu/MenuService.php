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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 *
 */

declare(strict_types = 1);

namespace oat\tao\model\menu;

use oat\oatbox\service\ConfigurableService;

/**
 * Class MenuService
 * @package oat\tao\model\menu
 */
class MenuService extends ConfigurableService
{
    const SERVICE_ID = 'tao/MenuService';

    /**
     * Get the structure content (from the structure.xml file) of each extension filtered by group.
     * @param $groupId
     * @return array
     */
    public function getPerspectivesByGroup($groupId)
    {
        $perspectives = [];
        foreach ($this->getAllPerspectives() as $perspective) {
            if ($perspective->getGroup() === $groupId) {
                $perspectives[] = $perspective;
            }
        }
        return $perspectives;
    }

    /**
     * Get the perspective for the extension/section in parameters
     * or null if not found
     */
    public function getPerspective($extension, $perspectiveId)
    {
        $returnValue = null;

        foreach ($this->getAllPerspectives() as $perspective) {
            if ($perspective->getId() == $perspectiveId) {
                $returnValue = $perspective;
                break;
            }
        }
        if (empty($returnValue)) {
            \common_logger::w('Structure ' . $perspectiveId . ' not found for extension ' . $extension);
        }

        return $returnValue;
    }

    /**
     * Get the structure content (from the structure.xml file) of each extension.
     * @return array
     */
    public function getAllPerspectives()
    {
        return [];
    }

    public function flushCache()
    {
        $this->getServiceLocator()->get('generis/cache')->remove(self::CACHE_KEY);
    }

    /**
     * Get perspective data depending on the group set in structure.xml
     *
     * @param $groupId
     * @return array
     */
    public function getNavigationElementsByGroup($groupId)
    {
        return [];
    }

    /**
     * Get the sections of the current extension's structure
     *
     * @param string $shownExtension
     * @param string $shownStructure
     * @param $user
     * @return array the sections
     */
    public function getSections($shownExtension, $shownStructure, $user)
    {
        return [];
    }


}
