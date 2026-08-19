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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2020-2026 (original work) Open Assessment Technologies SA;
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

    public function testDefaultSearchState(): void
    {
        $this->assertSame('', $this->subject->getQuery());
        $this->assertFalse($this->subject->hasQuery());
        $this->assertSame(DirectorySearchQuery::SORT_LABEL, $this->subject->getSortBy());
        $this->assertSame('asc', $this->subject->getSortDir());
        $this->assertSame(DirectorySearchQuery::DEFAULT_PAGE, $this->subject->getPage());
        $this->assertSame(DirectorySearchQuery::DEFAULT_PAGE_SIZE, $this->subject->getPageSize());
    }

    public function testSetQueryTrimsValueAndIgnoresWhitespaceOnly(): void
    {
        $this->assertSame($this->subject, $this->subject->setQuery('  color bars  '));
        $this->assertSame('color bars', $this->subject->getQuery());
        $this->assertTrue($this->subject->hasQuery());

        $this->subject->setQuery(" \t\n ");
        $this->assertSame('', $this->subject->getQuery());
        $this->assertFalse($this->subject->hasQuery());
    }

    public function testSetSortByAcceptsKnownFieldsAndFallsBack(): void
    {
        $this->assertSame($this->subject, $this->subject->setSortBy(DirectorySearchQuery::SORT_LOCATION));
        $this->assertSame(DirectorySearchQuery::SORT_LOCATION, $this->subject->getSortBy());

        $this->subject->setSortBy(DirectorySearchQuery::SORT_UPDATED_AT);
        $this->assertSame(DirectorySearchQuery::SORT_UPDATED_AT, $this->subject->getSortBy());

        $this->subject->setSortBy('unknown');
        $this->assertSame(DirectorySearchQuery::SORT_LABEL, $this->subject->getSortBy());
    }

    public function testSetSortDirNormalizesAscendingAndDescending(): void
    {
        $this->assertSame($this->subject, $this->subject->setSortDir('DESC'));
        $this->assertSame('desc', $this->subject->getSortDir());

        $this->subject->setSortDir('asc');
        $this->assertSame('asc', $this->subject->getSortDir());

        $this->subject->setSortDir('sideways');
        $this->assertSame('asc', $this->subject->getSortDir());
    }

    public function testSetPageAndPageSizeFallBackForNonPositiveValues(): void
    {
        $this->assertSame($this->subject, $this->subject->setPage(3));
        $this->assertSame(3, $this->subject->getPage());
        $this->subject->setPage(0);
        $this->assertSame(DirectorySearchQuery::DEFAULT_PAGE, $this->subject->getPage());
        $this->subject->setPage(-4);
        $this->assertSame(DirectorySearchQuery::DEFAULT_PAGE, $this->subject->getPage());

        $this->assertSame($this->subject, $this->subject->setPageSize(25));
        $this->assertSame(25, $this->subject->getPageSize());
        $this->subject->setPageSize(0);
        $this->assertSame(DirectorySearchQuery::DEFAULT_PAGE_SIZE, $this->subject->getPageSize());
        $this->subject->setPageSize(-2);
        $this->assertSame(DirectorySearchQuery::DEFAULT_PAGE_SIZE, $this->subject->getPageSize());
    }

    public function testSetDepthKeepsConfiguredValue(): void
    {
        $this->assertSame($this->subject, $this->subject->setDepth(7));
        $this->assertSame(7, $this->subject->getDepth());
    }

    public function testSetDepthNormalizesNonPositiveValues(): void
    {
        $this->assertSame($this->subject, $this->subject->setDepth(0));
        $this->assertSame(1, $this->subject->getDepth());

        $this->subject->setDepth(-2);
        $this->assertSame(1, $this->subject->getDepth());
    }
}
