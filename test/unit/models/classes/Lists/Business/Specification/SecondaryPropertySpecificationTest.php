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

namespace oat\tao\test\unit\model\Lists\Business\Specification;

use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;

class SecondaryPropertySpecificationTest extends TestCase
{
    /** @var SecondaryPropertySpecification */
    private $sut;

    /** @var DependentPropertySpecification */
    private $dependentPropertySpecification;

    protected function setUp(): void
    {
        $this->dependentPropertySpecification = $this->createMock(DependentPropertySpecification::class);

        $this->sut = new SecondaryPropertySpecification(
            $this->dependentPropertySpecification
        );
    }

    public function testIsSatisfiedBy(): void
    {
        $this->markTestIncomplete('TODO');
    }
}
