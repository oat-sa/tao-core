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
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\helpers\form\validators;

use PHPUnit\Framework\TestCase;
use tao_helpers_form_validators_Integer;

class IntegerValidatorTest extends TestCase
{
    /**
     * @var tao_helpers_form_validators_Integer
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new tao_helpers_form_validators_Integer();
    }

    /**
     * @dataProvider evaluationValues
     */
    public function testEvaluate($value, bool $isValid): void
    {
        $this->assertEquals($isValid, $this->subject->evaluate($value));
    }

    public function evaluationValues(): array
    {
        return [
            ['', true],
            ['0', true],
            ['123', true],
            [123, true],
            [1.2, false],
            ['1.2', false],
            ['0.2', false],
            ['abc', false],
            [[''], false],
            [[], false],
            ['@', false],
        ];
    }
}
