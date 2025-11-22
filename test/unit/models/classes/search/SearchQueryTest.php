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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search;

use oat\tao\model\search\SearchQuery;
use PHPUnit\Framework\TestCase;

class SearchQueryTest extends TestCase
{
    /** @var SearchQuery */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new SearchQuery(
            'user input',
            'rootClass',
            'parentClass',
            1,
            10,
            1,
            'label',
            'ASC'
        );
    }

    public function testsGetters(): void
    {
        $this->assertEquals('user input', $this->subject->getTerm());
        $this->assertEquals('rootClass', $this->subject->getRootClass());
        $this->assertEquals('parentClass', $this->subject->getParentClass());
        $this->assertEquals(1, $this->subject->getStartRow());
        $this->assertEquals(10, $this->subject->getRows());
        $this->assertEquals(1, $this->subject->getPage());
        $this->assertEquals('label', $this->subject->getSortBy());
        $this->assertEquals('ASC', $this->subject->getSortOrder());
    }
}
