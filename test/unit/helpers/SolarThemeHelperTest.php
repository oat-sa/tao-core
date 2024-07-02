<?php

namespace oat\tao\test\unit\helpers;

use oat\generis\test\TestCase;
use oat\tao\helpers\LayoutHelper;
use oat\tao\helpers\translation\SolarThemeHelper;

class SolarThemeHelperTest extends TestCase
{
    public function testIsContainPrefix(): void
    {
        $layoutHelper = $this->createMock(LayoutHelper::class);

        $solarThemeHelper = new SolarThemeHelper($layoutHelper);
        $this->assertTrue($solarThemeHelper->isContainPostfix('de-DE'));
    }

    public function testCheckPrefix(): void
    {
        $layoutHelper = $this->createMock(LayoutHelper::class);

        $locale = 'de-DE';

        $solarThemeHelper = new SolarThemeHelper($layoutHelper);
        $this->assertEquals($locale, $solarThemeHelper->checkPostfix($locale));
    }
}
