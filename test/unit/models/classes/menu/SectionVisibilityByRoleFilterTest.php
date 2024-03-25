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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\menu;

use oat\tao\model\menu\SectionVisibilityByRoleFilter;
use PHPUnit\Framework\TestCase;

class SectionVisibilityByRoleFilterTest extends TestCase
{
    public function testSectionVisibilityWhenNoRoleRestrictionsExist(): void
    {
        $filter = new SectionVisibilityByRoleFilter([]);
        $this->assertTrue($filter->isVisible(['admin'], 'section1'));
    }

    public function testSectionVisibilityWhenRoleRestrictionsExistButDoNotMatch(): void
    {
        $filter = new SectionVisibilityByRoleFilter(['section1' => ['guest']]);
        $this->assertTrue($filter->isVisible(['admin'], 'section1'));
    }

    public function testSectionVisibilityWhenRoleRestrictionsExistAndMatch(): void
    {
        $filter = new SectionVisibilityByRoleFilter(['section1' => ['admin']]);
        $this->assertFalse($filter->isVisible(['admin'], 'section1'));
    }

    public function testSectionVisibilityWhenMultipleRoleRestrictionsExistAndOneMatches(): void
    {
        $filter = new SectionVisibilityByRoleFilter(['section1' => ['admin', 'editor']]);
        $this->assertFalse($filter->isVisible(['admin', 'guest'], 'section1'));
    }

    public function testSectionVisibilityWhenMultipleRoleRestrictionsExistAndNoneMatch(): void
    {
        $filter = new SectionVisibilityByRoleFilter(['section1' => ['admin', 'editor']]);
        $this->assertTrue($filter->isVisible(['guest', 'user'], 'section1'));
    }
}
