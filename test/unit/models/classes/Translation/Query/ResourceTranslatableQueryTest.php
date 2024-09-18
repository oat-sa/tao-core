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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Translation\Query;

use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use PHPUnit\Framework\TestCase;

class ResourceTranslatableQueryTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $resourceType = 'http://www.tao.lu/Ontologies/TAO.rdf#AssessmentContentObject';
        $uniqueIds = ['id1', 'id2'];

        $query = new ResourceTranslatableQuery($resourceType, $uniqueIds);

        $this->assertSame($resourceType, $query->getResourceUri());
        $this->assertSame($uniqueIds, $query->getUniqueIds());
    }
}
