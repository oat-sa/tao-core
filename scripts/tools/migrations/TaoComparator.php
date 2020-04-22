<?php

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
