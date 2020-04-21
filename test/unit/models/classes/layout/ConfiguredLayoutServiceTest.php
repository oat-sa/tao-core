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
 * Copyright (c) 2020  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */
declare(strict_types=1);

namespace oat\tao\test\unit\models\layout;

use Context;
use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\tao\model\layout\configuredLayout\LayoutPageTitleService;
use oat\tao\model\layout\ConfiguredLayoutService;
use Request;

class ConfiguredLayoutServiceTest extends TestCase
{

    /**
     * @var ConfiguredLayoutService
     */
    private $layoutService;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MockObject|LayoutPageTitleService $layoutPageServiceMock */
        $layoutPageServiceMock = $this->createMock(LayoutPageTitleService::class);
        $layoutPageServiceMock->method('getTitle')->willReturn('page title');

        $serviceLocatorMock = $this->getServiceLocatorMock([
            LayoutPageTitleService::class => $layoutPageServiceMock
        ]);

        $this->layoutService = new ConfiguredLayoutService([
            ConfiguredLayoutService::OPTION_PAGE_TITLE_SERVICE => LayoutPageTitleService::class,
        ]);

        $this->layoutService->setServiceLocator($serviceLocatorMock);
        $context = $this->createMock(Context::class);
        $context->method('getModuleName')->willReturn('');
        $context->method('getActionName')->willReturn('');
        $request = $this->createMock(Request::class);
        $context->method('getRequest')->willReturn($request);

        $this->layoutService->setContext($context);
    }

    public function testGetPageTitle(): void
    {
        $this->assertSame('page title', $this->layoutService->getPageTitle());
    }
}
