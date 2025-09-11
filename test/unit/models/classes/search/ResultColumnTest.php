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
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search;

use oat\tao\model\search\ResultColumn;
use PHPUnit\Framework\TestCase;

class ResultColumnTest extends TestCase
{
    /** @var ResultColumn */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ResultColumn(
            'id',
            'sortId',
            'label',
            'type',
            'alias',
            'classLabel',
            true,
            true,
            true
        );
    }

    public function testsGetters(): void
    {
        $this->assertSame('id', $this->subject->getId());
        $this->assertSame('sortId', $this->subject->getSortId());
        $this->assertSame('label', $this->subject->getLabel());
        $this->assertSame('classLabel', $this->subject->getClassLabel());
        $this->assertSame('alias', $this->subject->getAlias());
        $this->assertSame('type', $this->subject->getType());
        $this->assertSame(true, $this->subject->isSortable());
        $this->assertSame(true, $this->subject->isDefault());
        $this->assertSame(true, $this->subject->isDuplicated());
    }
}
