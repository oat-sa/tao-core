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
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Domain;

use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\ClassMetadataSearchRequest;

class ClassMetadataSearchRequestTest extends TestCase
{
    /** @var ClassMetadataSearchRequest */
    private $sut;

    /**
     * @before
     */
    public function init(): void
    {
        $this->sut = new ClassMetadataSearchRequest();
    }

    public function testWithClassUri(): void
    {
        $classUri = 'https://example.com';

        $this->sut->setClassUri($classUri);

        $this->assertTrue($this->sut->hasClassUri());
        $this->assertSame($classUri, $this->sut->getClassUri());
    }

    public function testCustomLimit(): void
    {
        $limit = 1;

        $this->sut->setMaxListSize($limit);

        $this->assertSame($limit, $this->sut->getMaxListSize());
    }
}
