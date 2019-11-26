<?php

declare(strict_types=1);

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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\integration;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use helpers_Random;
use oat\generis\model\GenerisRdf;
use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\search\index\OntologyIndexService;
use oat\tao\model\search\Search;
use oat\tao\model\search\strategy\GenerisSearch;
use oat\tao\model\search\tokenizer\RawValue;
use oat\tao\model\search\tokenizer\Tokenizer;

/**
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 */
class SearchTest extends GenerisPhpUnitTestRunner
{
    private $class;

    private $property;

    protected function setUp(): void
    {
        parent::setUp();
        $rdfClass = new core_kernel_classes_Class(GenerisRdf::CLASS_GENERIS_RESOURCE);
        $this->class = $rdfClass->createSubClass('test class');
        $this->property = $this->class->createProperty('test property');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->class->delete();
        $this->property->delete();
    }

    public function testSearchService(): void
    {
        $implementation = new GenerisSearch();
        $this->assertInstanceOf(Search::class, $implementation);
    }

    public function testCreateIndex()
    {
        $tokenizer = new core_kernel_classes_Resource(RawValue::URI);
        $id = 'test_index_' . helpers_Random::generateString(8);

        $index = OntologyIndexService::createIndex($this->property, $id, $tokenizer, true, true);

        $this->assertInstanceOf(OntologyIndex::class, $index);
        $this->assertTrue($index->exists());

        $indexToo = OntologyIndexService::getIndexById($id);
        $this->assertInstanceOf(OntologyIndex::class, $indexToo);
        $this->assertTrue($index->equals($indexToo));

        $this->assertSame($id, $index->getIdentifier());
        $this->assertTrue($index->isDefaultSearchable());
        $this->assertTrue($index->isFuzzyMatching());

        $tokenizer = $index->getTokenizer();
        $this->assertInstanceOf(Tokenizer::class, $tokenizer);

        $indexes = OntologyIndexService::getIndexes($this->property);
        $this->assertTrue(is_array($indexes));
        $this->assertSame(1, count($indexes));

        $indexToo = reset($indexes);
        $this->assertInstanceOf(OntologyIndex::class, $indexToo);
        $this->assertTrue($index->equals($indexToo));

        return $index;
    }

    /**
     * @expectedException common_Exception
     * @depends testCreateIndex
     */
    public function testDublicateCreate($index): void
    {
        $this->assertInstanceOf(OntologyIndex::class, $index);

        $tokenizer = new core_kernel_classes_Resource(RawValue::URI);
        OntologyIndexService::createIndex($this->property, $index->getIdentifier(), $tokenizer, true, true);
    }

    /**
     * @depends testCreateIndex
     */
    public function testCreateSimilar($index)
    {
        $this->assertInstanceOf(OntologyIndex::class, $index);

        $tokenizer = new core_kernel_classes_Resource(RawValue::URI);
        $similar = OntologyIndexService::createIndex($this->property, substr($index->getIdentifier(), 0, -2), $tokenizer, true, true);
        $this->assertInstanceOf(OntologyIndex::class, $similar);

        return $similar;
    }

    /**
     * @depends testCreateSimilar
     */
    public function testDeleteSimilar($index): void
    {
        $this->assertInstanceOf(OntologyIndex::class, $index);
        $this->assertTrue($index->exists());
        $index->delete();
        $this->assertFalse($index->exists());
    }

    /**
     * @depends testCreateIndex
     * @depends testCreateSimilar
     * @depends testDublicateCreate
     */
    public function testDeleteIndex($index): void
    {
        $this->assertInstanceOf(OntologyIndex::class, $index);
        $this->assertTrue($index->exists());
        $index->delete();
        $this->assertFalse($index->exists());
    }
}
