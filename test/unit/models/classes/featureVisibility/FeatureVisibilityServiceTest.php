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

namespace oat\tao\unit\test\model\featureVisibility;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use oat\oatbox\AbstractRegistry;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\featureVisibility\FeatureVisibilityService;

class FeatureVisibilityServiceTest extends TestCase
{
    /** @var AbstractRegistry */
    private $abstractRegistryStub;

    /** @var FeatureVisibilityService */
    private $featureVisibilityService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abstractRegistryStub = new class () extends ClientLibConfigRegistry {
            private $config = [];

            public function get($key)
            {
                if (!array_key_exists($key, $this->config)) {
                    return '';
                }
                return $this->config[$key];
            }

            public function set($key, $value)
            {
                $this->config[$key] = $value;
            }
        };
        $this->featureVisibilityService = new FeatureVisibilityService($this->abstractRegistryStub);
    }

    public function testShowFeatureSetsFeatureToTheShowStatus()
    {
        $featureName = 'item1/customInteraction/*';

        $this->featureVisibilityService->showFeature($featureName);

        $resultConfig = $this->abstractRegistryStub->get('services/features');
        $this->assertEquals(
            FeatureVisibilityService::SHOW_PARAM,
            $resultConfig['visibility'][$featureName]
        );
    }

    public function testHideFeatureSetsFeatureToTheHideStatus()
    {
        $featureName = 'item/multiColumn';

        $this->featureVisibilityService->hideFeature($featureName);

        $resultConfig = $this->abstractRegistryStub->get('services/features');

        $this->assertEquals(
            FeatureVisibilityService::HIDE_PARAM,
            $resultConfig['visibility'][$featureName]
        );
    }

    public function testSetFeaturesVisibilitySetsFeaturesToTheGivenStatusesAndDoesNotRemovePreviouslySetFeatures()
    {
        $singleFeatureName = 'item/multiColumn';

        $featuresMap = [
            "item/customInteraction/audioPciInteraction" => FeatureVisibilityService::SHOW_PARAM,
            "test/item/timeLimits" => FeatureVisibilityService::HIDE_PARAM,
        ];

        $this->featureVisibilityService->showFeature($singleFeatureName);
        $this->featureVisibilityService->setFeaturesVisibility($featuresMap);

        $resultConfig = $this->abstractRegistryStub->get('services/features');
        $this->assertEquals(
            $featuresMap + [$singleFeatureName => FeatureVisibilityService::SHOW_PARAM],
            $resultConfig['visibility']
        );
    }

    public function testSetFeaturesVisibilityThrowsExceptionInCaseOfWrongStatus()
    {
        $wrongFeatureStatus = 'wrongStatus';

        $this->expectException(InvalidArgumentException::class);

        $this->featureVisibilityService->setFeaturesVisibility(['featureName' => $wrongFeatureStatus]);
    }

    public function testRemoveFeatureRemovesFeatureFromConfig()
    {
        $featureName = 'featureName';

        $this->featureVisibilityService->showFeature($featureName);
        $this->featureVisibilityService->removeFeature($featureName);

        $resultConfig = $this->abstractRegistryStub->get('services/features');

        $this->assertEmpty($resultConfig['visibility']);
    }

    public function testAllFeatureVisibilityMethodsWorkWithoutIssuesBeingCalledSeveralTimes()
    {
        $featureNameOne = 'feature1';
        $featureNameTwo = 'feature2';
        $featureNameThree = 'feature3';
        $featureNameFour = 'feature4';

        for ($i = 0; $i < 3; $i++) {
            $this->featureVisibilityService->showFeature($featureNameOne);

            $this->featureVisibilityService->hideFeature($featureNameTwo);

            $this->featureVisibilityService->setFeaturesVisibility([
                $featureNameThree => FeatureVisibilityService::SHOW_PARAM,
                $featureNameFour => FeatureVisibilityService::HIDE_PARAM
            ]);

            $this->featureVisibilityService->removeFeature($featureNameFour);
        }

        $resultConfig = $this->abstractRegistryStub->get('services/features');

        $this->assertEquals(
            [
                $featureNameOne => FeatureVisibilityService::SHOW_PARAM,
                $featureNameTwo => FeatureVisibilityService::HIDE_PARAM,
                $featureNameThree => FeatureVisibilityService::SHOW_PARAM,
            ],
            $resultConfig['visibility']
        );
    }
}
