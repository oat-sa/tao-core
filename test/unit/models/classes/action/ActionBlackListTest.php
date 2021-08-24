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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\action;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\action\ActionBlackList;
use oat\tao\model\featureFlag\FeatureFlagChecker;

class ActionBlackListTest extends TestCase
{
    /** @var ActionBlackList */
    private $subject;

    /** @var ActionBlackList|MockObject */
    private $actionBlackListMock;

    public function setUp(): void
    {
        $this->actionBlackListMock = $this->createMock(FeatureFlagChecker::class);
        $this->subject = new ActionBlackList();
        $this->subject->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    FeatureFlagChecker::class => $this->actionBlackListMock
                ]
            )
        );

        $this->actionBlackListMock
            ->method('isEnabled')
            ->with('DISABLE_ACTION_BY_FEATURE_FLAG')
            ->willReturn(true);

        $this->subject->setOption(ActionBlackList::OPTION_DISABLED_ACTIONS, [
            'some-action-to-disable'
        ]);

        $this->subject->setOption(ActionBlackList::OPTION_DISABLED_ACTIONS_FLAG_MAP, [
            'some-action-disabled-by-feature-flag' => 'DISABLE_ACTION_BY_FEATURE_FLAG'
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
            'action not disabled' => ['some-action-that-is-not-disabled', false]
        ];
    }
}
