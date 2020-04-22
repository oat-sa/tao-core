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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\migrations;

use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\Version;
use common_ext_ExtensionsManager as ExtensionsManager;
use common_ext_Extension as Extension;

/**
 * Class TaoComparator
 * @package oat\tao\scripts\tools\migrations
 */
class TaoComparator implements Comparator
{
    private $extensionsManager;

    /**
     * TaoComparator constructor.
     * @param ExtensionsManager $extensionsManager
     */
    public function __construct(ExtensionsManager $extensionsManager)
    {
        $this->extensionsManager = $extensionsManager;
    }

    public function compare(Version $a, Version $b) : int
    {
        $merged = array_merge(
            $this->extensionsManager->getInstalledExtensions(),
            $this->getMissingExtensions()
        );
        $sortedExtensions = array_flip(array_keys(\helpers_ExtensionHelper::sortByDependencies($merged)));
        $versionA = (string) $a;
        $versionB = (string) $b;
        preg_match('/.*Version(\d+)_(.*)$/', $versionA, $matchesA);
        preg_match('/.*Version(\d+)_(.*)$/', $versionB, $matchesB);
        list($aClass, $aTime, $aExt) = $matchesA;
        list($bClass, $bTime, $bExt) = $matchesB;
        if ($aExt === $bExt) {
            return ((int) $aTime) - ((int) $bTime);
        }
        return $sortedExtensions[$aExt] - $sortedExtensions[$bExt];
    }

    /**
     * @return Extension[]
     */
    protected function getMissingExtensions()
    {
        /** @var ExtensionsManager $extManager */
        return array_map(function ($extId) {
            return $this->extensionsManager->getExtensionById($extId);
        }, \helpers_ExtensionHelper::getMissingExtensionIds($this->extensionsManager->getInstalledExtensions()));
    }
}
