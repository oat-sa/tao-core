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

namespace oat\tao\model\media\mediaSource;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use oat\tao\model\media\MediaAsset;

class DirectorySearchQueryTest extends TestCase
{
    /** @var DirectorySearchQuery */
    private $subject;

    protected function setUp(): void
    {
        /** @var MockObject|MediaAsset $assetMock */
        $assetMock = $this->createMock(MediaAsset::class);
        $assetMock->method('getMediaIdentifier')->willReturn('mediaIdentifier');
        $this->subject = new DirectorySearchQuery($assetMock, 'uri', 'lang', ['a' => 'b'], 3, 11, 10);
    }

    public function testGetters()
    {
        $this->assertSame($this->subject->getParentLink(), 'mediaIdentifier');
        $this->assertSame($this->subject->getDepth(), 3);
        $this->assertSame($this->subject->getItemLang(), 'lang');
        $this->assertSame($this->subject->getItemUri(), 'uri');
        $this->assertSame($this->subject->getFilter(), ['a' => 'b']);
        $this->assertSame($this->subject->getChildrenLimit(), 10);
        $this->assertSame($this->subject->getChildrenOffset(), 11);
    }
}
