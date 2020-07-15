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
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Lists\Business\Domain;

use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use TypeError;

class ValueCollectionSearchRequestTest extends TestCase
{
    /** @var ValueCollectionSearchRequest */
    private $sut;

    /**
     * @before
     */
    public function init(): void
    {
        $this->sut = new ValueCollectionSearchRequest();
    }

    public function testBareSearchRequest(): void
    {
        $this->assertFalse($this->sut->hasPropertyUri());
        $this->assertFalse($this->sut->hasValueCollectionUri());
        $this->assertFalse($this->sut->hasSubject());
        $this->assertFalse($this->sut->hasExcluded());
        $this->assertEmpty($this->sut->getExcluded());
        $this->assertFalse($this->sut->hasLimit());
    }

    /**
     * @param string $method
     *
     * @testWith ["getPropertyUri"]
     *           ["getValueCollectionUri"]
     *           ["getSubject"]
     *           ["getLimit"]
     */
    public function testNotInitializedGetterCall(string $method): void
    {
        $this->expectException(TypeError::class);

        $this->sut->$method();
    }

    public function testWithPropertyUri(): void
    {
        $propertyUri = 'https://example.com';

        $this->sut->setPropertyUri($propertyUri);

        $this->assertTrue($this->sut->hasPropertyUri());
        $this->assertSame($propertyUri, $this->sut->getPropertyUri());
    }

    public function testWithValueCollectionUri(): void
    {
        $propertyUri = 'https://example.com';

        $this->sut->setValueCollectionUri($propertyUri);

        $this->assertTrue($this->sut->hasValueCollectionUri());
        $this->assertSame($propertyUri, $this->sut->getValueCollectionUri());
    }

    public function testWithSubject(): void
    {
        $subject = 'search value';

        $this->sut->setSubject($subject);

        $this->assertTrue($this->sut->hasSubject());
        $this->assertSame($subject, $this->sut->getSubject());
    }

    public function testWithExcluded(): void
    {
        $excluded = [
            'https_2_example_0_com_3_1',
            'https_2_example_0_com_3_2',
        ];

        foreach ($excluded as $uri) {
            $this->sut->addExcluded($uri);
        }

        $this->assertTrue($this->sut->hasExcluded());
        $this->assertSame($excluded, $this->sut->getExcluded());
    }

    public function testCustomLimit(): void
    {
        $limit = 1;

        $this->sut->setLimit($limit);

        $this->assertSame($limit, $this->sut->getLimit());
    }
}
