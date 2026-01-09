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

namespace oat\tao\test\unit\models\classes\Translation\Service;

use oat\tao\model\Translation\Entity\ResourceCollection;
use oat\tao\model\Translation\Exception\ResourceTranslationException;
use oat\tao\model\Translation\Query\ResourceTranslatableQuery;
use oat\tao\model\Translation\Repository\ResourceTranslatableRepository;
use oat\tao\model\Translation\Service\ResourceTranslatableRetriever;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ResourceTranslatableRetrieverTest extends TestCase
{
    private ResourceTranslatableRetriever $sut;

    /** @var ResourceTranslatableRepository|MockObject */
    private $resourceTranslatableRepository;

    /** @var MockObject|ServerRequestInterface */
    private $request;

    protected function setUp(): void
    {
        $this->resourceTranslatableRepository = $this->createMock(ResourceTranslatableRepository::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->sut = new ResourceTranslatableRetriever($this->resourceTranslatableRepository);
    }

    public function testGetByRequest(): void
    {
        $this->request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn(
                [
                    'id' => 'id',
                ]
            );

        $result = new ResourceCollection();

        $this->resourceTranslatableRepository
            ->expects($this->once())
            ->method('find')
            ->with(new ResourceTranslatableQuery('id'))
            ->willReturn($result);

        $this->assertSame($result, $this->sut->getByRequest($this->request));
    }

    public function testGetByRequestRequiresResourceId(): void
    {
        $this->request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);

        $this->expectException(ResourceTranslationException::class);
        $this->expectExceptionMessage('Resource id is required');

        $this->sut->getByRequest($this->request);
    }
}
