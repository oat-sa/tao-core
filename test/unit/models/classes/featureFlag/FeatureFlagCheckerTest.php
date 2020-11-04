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
 */

declare(strict_types=1);

namespace oat\tao\unit\test\model\featureFlag;

use oat\generis\test\TestCase;
use oat\tao\model\featureFlag\FeatureFlagChecker;

class FeatureFlagCheckerTest extends TestCase
{
    /** @var FeatureFlagChecker */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new FeatureFlagChecker();
        $_ENV['FEATURE'] = true;
        $_ENV['FEATURE_DISABLED'] = false;
    }

    public function testIsEnabled(): void
    {
        self::assertTrue($this->subject->isEnabled('FEATURE'));
    }

    public function testIsNotEnabled(): void
    {
        self::assertFalse($this->subject->isEnabled('NOT_ENABLED_FEATURE'));
    }

    public function testIsEnabledOnDisabledFeature(): void
    {
        self::assertFalse($this->subject->isEnabled('FEATURE_DISABLED'));
    }
}
