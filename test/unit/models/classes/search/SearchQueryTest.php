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

namespace oat\tao\test\unit\model\search;

use oat\generis\test\TestCase;
use oat\tao\model\search\SearchQuery;

class SearchQueryTest extends TestCase
{
    /** @var SearchQuery */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new SearchQuery(
            'user input',
            'rootClass',
            'parentClass',
            1,
            10,
            1
        );
    }

    public function testsGetTerm(): void
    {
        $this->assertEquals('user input', $this->subject->getTerm());
    }

    public function testsGetRootClass(): void
    {
        $this->assertEquals('rootClass', $this->subject->getRootClass());
    }

    public function testsGetParentClass(): void
    {
        $this->assertEquals('parentClass', $this->subject->getParentClass());
    }

    public function testsGetStartRow(): void
    {
        $this->assertEquals(1, $this->subject->getStartRow());
    }

    public function testsGetRows(): void
    {
        $this->assertEquals(10, $this->subject->getRows());
    }

    public function testsGetPage(): void
    {
        $this->assertEquals(1, $this->subject->getPage());
    }
}
