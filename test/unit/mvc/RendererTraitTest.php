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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\test\unit\model\mvc;

use PHPUnit\Framework\TestCase;
use oat\tao\model\mvc\RendererTrait;

class RendererTraitTest extends TestCase
{
    public function testGetRenderer()
    {
        $service = new ProxyMockForRendererTrait();
        $this->assertInstanceOf(\Renderer::class, $service->getRenderer());
    }

    public function testSetHasView()
    {
        $service = new ProxyMockForRendererTrait();
        $service->setView('fixture');
        $this->assertTrue($service->hasView());
    }

    public function testSetData()
    {
        $service = new ProxyMockForRendererTrait();
        $service->setData('fixture-key', 'fixture-value');

        $property = new \ReflectionProperty(\Renderer::class, 'variables');
        $property->setAccessible(true);

        $variables = $property->getValue($service->getRenderer());
        $this->assertCount(1, $variables);
        $this->assertArrayHasKey('fixture-key', $variables);
        $this->assertEquals('fixture-value', $variables['fixture-key']);
    }

    public function testNotViewIfNoRenderer()
    {
        $service = new ProxyMockForRendererTrait();
        $this->assertFalse($service->hasView());
    }
}

class ProxyMockForRendererTrait
{
    use RendererTrait;
}
