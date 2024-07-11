<?php

namespace oat\tao\test\unit\helpers;

use oat\generis\test\TestCase;
use oat\tao\helpers\translation\SolarThemeHelper;

class SolarThemeHelperTest extends TestCase
{
    public function testIsContainPrefix(): void
    {
        $solarThemeHelper = $this->createMock(SolarThemeHelper::class);

        $solarThemeHelper
            ->method('isContainPostfix')
            ->with('de-DE')
            ->willReturn(true);

        $this->assertTrue($solarThemeHelper->isContainPostfix('de-DE'));
    }

    public function testCheckPrefix(): void
    {
        $locale = 'de-DE';

        $solarThemeHelper = $this->createMock(SolarThemeHelper::class);

        $solarThemeHelper
            ->method('checkPostfix')
            ->with($locale)
            ->willReturn($locale);

        $this->assertEquals($locale, $solarThemeHelper->checkPostfix($locale));
    }
}
