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
 * Copyright (c) 2015-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\requiredAction;

use oat\tao\model\requiredAction\implementation\RequiredActionRedirectUrlPart;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\requiredAction\implementation\TimeRule;
use oat\tao\model\requiredAction\RequiredActionInterface;
use PHPUnit\Framework\TestCase;

class RequiredActionRedirectUrlPartTest extends TestCase
{
    /**
     * @dataProvider optionsProvider
     */
    public function testMustBeExecuted($result, $rules)
    {
        $action = new RequiredActionRedirectUrlPart('testAction', $rules, ['url']);
        $this->assertEquals($action->mustBeExecuted(), $result);
    }

    /**
     * @return array
     */
    public function optionsProvider()
    {
        return [
            [//no rules
                'result' => false,
                'rules' => [],
            ],
            [//one rule which returns false
                'result' => false,
                'rules' => [
                    $this->getNegativeRule()
                ],
            ],
            [//one rule which returns true
                'result' => true,
                'rules' => [
                    $this->getPositiveRule(),
                ],
            ],
            [//two rules and one of them returns true
                'result' => true,
                'rules' => [
                    $this->getPositiveRule(),
                    $this->getNegativeRule()
                ],
            ],
        ];
    }

    private function getPositiveRule(): TimeRule|MockObject
    {
        $ruleMock = $this->createMock(TimeRule::class);
        $ruleMock
            ->method('setRequiredAction')
            ->with($this->isInstanceOf(RequiredActionInterface::class))
            ->willReturn(null);
        $ruleMock
            ->method('check')
            ->with(null)
            ->willReturn(true);

        return $ruleMock;
    }

    private function getNegativeRule(): TimeRule|MockObject
    {
        $ruleMock = $this->createMock(TimeRule::class);
        $ruleMock
            ->method('setRequiredAction')
            ->with($this->isInstanceOf(RequiredActionInterface::class))
            ->willReturn(null);
        $ruleMock
            ->method('check')
            ->with(null)
            ->willReturn(false);

        return $ruleMock;
    }
}
