<?php declare(strict_types=1);
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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\test\unit\models\classes\accessControl\func\implementation;

use oat\generis\test\TestCase;
use oat\tao\model\accessControl\func\implementation\CachedModel;
use oat\oatbox\cache\SimpleCache;
use oat\oatbox\user\User;
use oat\tao\model\accessControl\func\AclModelFactory;
use oat\tao\model\accessControl\func\AclModel;
use oat\generis\test\MockCacheTrait;
use oat\tao\model\accessControl\func\ControllerAccessRight;

class CachedModelTest extends TestCase
{
    use MockCacheTrait;
    /**
     * @var CachedModel
     */
    private $object;
    
    private $cacheCounter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new CachedModel();
        $controller1 = new ControllerAccessRight('controller1', 'fakeExtension');
        $controller1->addActionAccess('role1', 'action1');
        $controller1->addFullAccess('role2');
        $model = $this->prophesize(AclModel::class);
        $model->getControllerAcl('controller1', 'fakeExtension')->willReturn($controller1);
        $modelBuilder = $this->prophesize(AclModelFactory::class);
        $modelBuilder->buildModel()->willReturn($model->reveal());
        $this->cacheCounter = $this->getCache();
        $this->object->setServiceLocator($this->getServiceLocatorMock([
            SimpleCache::SERVICE_ID => $this->cacheCounter,
            AclModelFactory::class => $modelBuilder->reveal()
        ]));
    }
    
    public function testModel(): void
    {
        $prophet = $this->prophesize(User::class);
        $prophet->getRoles()->willReturn([]);
        $user = $prophet->reveal();

        $this->assertEquals(true, $this->object->hasAccess($user, 'controller1', 'action1', []));
        $this->assertEquals(true, $this->object->hasAccess($user, 'controller1', 'action1', []));

        $this->assertEquals(false, $this->object->hasAccess($user, 'controller2', 'action1', []));
        $this->assertEquals(false, $this->object->hasAccess($user, 'controller2', 'action1', []));
    }
}
