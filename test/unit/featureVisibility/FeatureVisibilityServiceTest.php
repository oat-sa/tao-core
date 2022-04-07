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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\test\unit\model\listener;

use common_ext_ExtensionsManager;
use Exception;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\featureVisibility\FeatureVisibilityService;

class FeatureVisibilityServiceTest extends TestCase
{
    /** @var common_ext_ExtensionsManager|MockObject */
    private $extManager;

    /** @var FeatureVisibilityService  */
    private $fv;

    protected function setUp(): void
    {
        parent::setUp();

        $extensionStub = new class() {
            private $config = [];

            public function getConfig($key)
            {
                return $this->config;
            }

            public function setConfig($key, $value)
            {
                $this->config = $value;
            }
        };
        $this->extManager = $this->createStub(common_ext_ExtensionsManager::class);
        $this->extManager->method('getExtensionById')->willReturn($extensionStub);

        $this->fv = new FeatureVisibilityService($this->extManager);
    }

    public function testShowFeature_SetsFeatureToTheShowStatus()
    {
        $featureName = 'item/customInteraction/*';

        $this->fv->showFeature($featureName);

        $resultConfig = $this->extManager->getExtensionById('extId')->getConfig('confId');
        $this->assertEquals(
            FeatureVisibilityService::SHOW_PARAM,
            $resultConfig['helpers/features']['visibility'][$featureName]
        );
    }

    public function testHideFeature_SetsFeatureToTheHideStatus()
    {
        $featureName = 'item/multiColumn';

        $this->fv->hideFeature($featureName);

        $resultConfig = $this->extManager->getExtensionById('extId')->getConfig('confId');

        $this->assertEquals(
            FeatureVisibilityService::HIDE_PARAM,
            $resultConfig['helpers/features']['visibility'][$featureName]
        );
    }

    public function testSetFeaturesVisibility_SetsFeaturesToTheGivenStatusesAndDoesNotRemovePreviouslySetFeatures()
    {
        $singleFeatureName = 'item/multiColumn';

        $featuresMap = [
            "item/customInteraction/audioPciInteraction" => FeatureVisibilityService::SHOW_PARAM,
            "test/item/timeLimits" => FeatureVisibilityService::HIDE_PARAM,
        ];

        $this->fv->showFeature($singleFeatureName);
        $this->fv->setFeaturesVisibility($featuresMap);

        $resultConfig = $this->extManager->getExtensionById('extId')->getConfig('confId');
        $this->assertEquals(
            $featuresMap + [$singleFeatureName => FeatureVisibilityService::SHOW_PARAM],
            $resultConfig['helpers/features']['visibility']
        );
    }

    public function testSetFeaturesVisibility_ThrowsExceptionInCaseOfWrongStatus()
    {
        $wrongFeatureStatus = 'wrongStatus';

        try {
            $this->fv->setFeaturesVisibility(['featureName' => $wrongFeatureStatus]);
        } catch (Exception $e) {
            $this->assertStringContainsString($wrongFeatureStatus, $e->getMessage());
        }
    }

    public function testRemoveFeature_RemovesFeatureFromConfig()
    {
        $featureName = 'featureName';

        $this->fv->showFeature($featureName);
        $this->fv->removeFeature($featureName);

        $resultConfig = $this->extManager->getExtensionById('extId')->getConfig('confId');

        $this->assertEmpty($resultConfig['helpers/features']['visibility']);
    }

    public function testAllFeatureVisibilityMethods_WorkWithoutIssuesBeingCalledSeveralTimes()
    {
        $featureNameOne = 'feature1';
        $featureNameTwo = 'feature2';
        $featureNameThree = 'feature3';
        $featureNameFour = 'feature4';

        for ($i = 0; $i < 3; $i++) {
            $this->fv->showFeature($featureNameOne);

            $this->fv->hideFeature($featureNameTwo);

            $this->fv->setFeaturesVisibility([
                $featureNameThree => FeatureVisibilityService::SHOW_PARAM,
                $featureNameFour => FeatureVisibilityService::HIDE_PARAM
            ]);

            $this->fv->removeFeature($featureNameFour);
        }

        $resultConfig = $this->extManager->getExtensionById('extId')->getConfig('confId');

        $this->assertEquals(
            [
                $featureNameOne => FeatureVisibilityService::SHOW_PARAM,
                $featureNameTwo => FeatureVisibilityService::HIDE_PARAM,
                $featureNameThree => FeatureVisibilityService::SHOW_PARAM,
            ],
            $resultConfig['helpers/features']['visibility']
        );
    }
}
