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

use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use TypeError;

class ValueCollectionSearchRequestTest extends TestCase
{
    /** @var ValueCollectionSearchRequest */
    private $subject;

    /**
     * @before
     */
    public function init(): void
    {
        $this->subject = new ValueCollectionSearchRequest();
    }

    public function testBareSearchRequest(): void
    {
        $this->assertFalse($this->subject->hasPropertyUri());
        $this->assertFalse($this->subject->hasValueCollectionUri());
        $this->assertFalse($this->subject->hasSubject());
        $this->assertFalse($this->subject->hasExcluded());
        $this->assertEmpty($this->subject->getExcluded());
        $this->assertFalse($this->subject->hasLimit());
        $this->assertFalse($this->subject->hasParentListValues());
        $this->assertEmpty($this->subject->getSelectedValues());
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

        $this->subject->$method();
    }

    public function testWithPropertyUri(): void
    {
        $propertyUri = 'https://example.com';

        $this->subject->setPropertyUri($propertyUri);

        $this->assertTrue($this->subject->hasPropertyUri());
        $this->assertSame($propertyUri, $this->subject->getPropertyUri());
    }

    public function testWithValueCollectionUri(): void
    {
        $propertyUri = 'https://example.com';

        $this->subject->setValueCollectionUri($propertyUri);

        $this->assertTrue($this->subject->hasValueCollectionUri());
        $this->assertSame($propertyUri, $this->subject->getValueCollectionUri());
    }

    public function testWithSubject(): void
    {
        $subject = 'search value';

        $this->subject->setSubject($subject);

        $this->assertTrue($this->subject->hasSubject());
        $this->assertSame($subject, $this->subject->getSubject());
    }

    public function testWithExcluded(): void
    {
        $excluded = [
            'https_2_example_0_com_3_1',
            'https_2_example_0_com_3_2',
        ];

        foreach ($excluded as $uri) {
            $this->subject->addExcluded($uri);
        }

        $this->assertTrue($this->subject->hasExcluded());
        $this->assertSame($excluded, $this->subject->getExcluded());
    }

    public function testCustomLimit(): void
    {
        $limit = 1;

        $this->subject->setLimit($limit);

        $this->assertSame($limit, $this->subject->getLimit());
    }

    public function testWithDataLanguage(): void
    {
        $dataLanguage = 'dummyValue';
        $newDataLanguage = 'otherDummyValue';

        $this->subject->setDataLanguage($dataLanguage);

        $this->assertTrue($this->subject->hasDataLanguage());
        $this->assertSame($dataLanguage, $this->subject->getDataLanguage());
        $this->assertNotEquals($newDataLanguage, $this->subject->getDataLanguage());
        $this->subject->setDataLanguage($newDataLanguage);
        $this->assertSame($newDataLanguage, $this->subject->getDataLanguage());
    }

    public function testWithParentListValues(): void
    {
        $this->subject->setParentListValues(...['value']);

        $this->assertTrue($this->subject->hasParentListValues());
        $this->assertContains('value', $this->subject->getParentListValues());
    }

    public function testWithSelectedValues(): void
    {
        $this->subject->setSelectedValues('value');

        $this->assertEquals(['value'], $this->subject->getSelectedValues());
    }
}
