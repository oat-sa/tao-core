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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\action;

use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\action\ActionBlackList;
use oat\tao\model\featureFlag\FeatureFlagChecker;

class ActionBlackListTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ActionBlackList $subject;
    private FeatureFlagChecker|MockObject $featureFlagChecker;

    protected function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $this->subject = new ActionBlackList();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    FeatureFlagChecker::class => $this->featureFlagChecker
                ]
            )
        );

        $this->featureFlagChecker
            ->method('isEnabled')
            ->with($this->callback(function ($envVarName) {
                return in_array($envVarName, ['FEATURE_FLAG_ENABLE_ONE_ACTION', 'DISABLE_ACTION_BY_FEATURE_FLAG']);
            }))
            ->willReturn(true);

        $this->subject->setOption(ActionBlackList::OPTION_DISABLED_ACTIONS, [
            'some-action-to-disable',
            'action-disabled-by-default-but-enabled-by-ff'
        ]);

        $this->subject->setOption(ActionBlackList::OPTION_DISABLED_ACTIONS_FLAG_MAP, [
            'some-action-disabled-by-feature-flag' => 'DISABLE_ACTION_BY_FEATURE_FLAG'
        ]);

        $this->subject->setOption(ActionBlackList::OPTION_ENABLED_ACTIONS_BY_FEATURE_FLAG_MAP, [
            'action-disabled-by-default-but-enabled-by-ff' => 'FEATURE_FLAG_ENABLE_ONE_ACTION'
        ]);
    }

    /**
     * @dataProvider getActionsMatrix
     */
    public function testIsDisabled($action, $result): void
    {
        $this->assertEquals($result, $this->subject->isDisabled($action));
    }

    public function getActionsMatrix()
    {
        return [
            'configurable service value' => ['some-action-to-disable', true],
            'feature flag value' => ['some-action-disabled-by-feature-flag', true],
            'action not disabled' => ['some-action-that-is-not-disabled', false],
            'disabled action toggled on by ff' => ['action-disabled-by-default-but-enabled-by-ff', false],
        ];
    }
}
