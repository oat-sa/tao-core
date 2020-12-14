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

namespace oat\tao\model\media\MediaSource;

use oat\generis\test\TestCase;

class QueryObjectTest extends TestCase
{
    /** @var QueryObject */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new QueryObject('link', ['a' => 'b'], 3, 4, 5);
    }

    public function testGetParentLink()
    {
        $this->assertSame($this->subject->getParentLink(), 'link');
    }

    public function testGetDepth()
    {
        $this->assertSame($this->subject->getDepth(), 3);
    }

    public function testGetFilter()
    {
        $this->assertSame($this->subject->getFilter(), ['a' => 'b']);
    }

    public function testGetChildrenLimit()
    {
        $this->assertSame($this->subject->getChildrenLimit(), 4);
    }

    public function testGetChildrenOffset()
    {
        $this->assertSame($this->subject->getChildrenOffset(), 5);
    }

}
