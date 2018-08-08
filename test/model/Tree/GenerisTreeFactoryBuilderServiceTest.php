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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\test\model\Tree;

use oat\tao\model\GenerisTreeFactory;
use oat\tao\model\Tree\GenerisTreeFactoryBuilderRequest;
use oat\tao\model\Tree\GenerisTreeFactoryBuilderService;

class GenerisTreeFactoryBuilderServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $service = new GenerisTreeFactoryBuilderService();
        $treeFactory = $service->build(
            new GenerisTreeFactoryBuilderRequest(true, [], 10, 0)
        );

        $this->assertInstanceOf(GenerisTreeFactory::class, $treeFactory);
    }

    public function testShowNoLabelIsOverwritten()
    {
        $service = new GenerisTreeFactoryBuilderService();
        $this->assertFalse($service->isShowNoLabel());

        $service->setShowLabel(true);
        $this->assertTrue($service->isShowNoLabel());
    }
}
