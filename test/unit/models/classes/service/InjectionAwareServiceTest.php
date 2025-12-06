<?php

/**
 * @noinspection UnusedConstructorDependenciesInspection
 * @noinspection PhpMissingParentConstructorInspection
 */

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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\service;

use PHPUnit\Framework\TestCase;
use oat\oatbox\service\ConfigurableService;

class EmptyConstructorInjectionAwareService extends InjectionAwareService
{
}

class PureInjectionAwareService extends InjectionAwareService
{
    private $var;

    public function __construct(string $var)
    {
        $this->var = $var;
    }
}

class InjectionAwareServiceWithVariadicParameters extends InjectionAwareService
{
    private $strings;
    private $int;

    public function __construct(int $int, string ...$strings)
    {
        $this->strings = $strings;
        $this->int = $int;
    }
}

class HostInjectionAwareService extends InjectionAwareService
{
    private $var;

    public function __construct(PureInjectionAwareService $var)
    {
        $this->var = $var;
    }
}

class PureConfigurableService extends ConfigurableService
{
}

class PureConfigurableServiceWithId extends ConfigurableService
{
    public const SERVICE_ID = 'service/id';
}

class HostPureConfigurableService extends InjectionAwareService
{
    private $var;

    public function __construct(PureConfigurableService $var)
    {
        $this->var = $var;
    }
}

class HostHostPureConfigurableService extends InjectionAwareService
{
    private $var;

    public function __construct(HostPureConfigurableService $var)
    {
        $this->var = $var;
    }
}

class HostMixService extends InjectionAwareService
{
    private $var;
    private $var2;

    public function __construct(PureConfigurableServiceWithId $var, PureInjectionAwareService $var2)
    {
        $this->var = $var;
        $this->var2 = $var2;
    }
}

class HostMixNestedService extends InjectionAwareService
{
    private $var;
    private $var2;
    private $var3;

    public function __construct(
        PureConfigurableService $var,
        HostPureConfigurableService $var2,
        PureConfigurableServiceWithId $var3
    ) {
        $this->var = $var;
        $this->var2 = $var2;
        $this->var3 = $var3;
    }
}

class InjectionAwareServiceTest extends TestCase
{
    public function testPureInjectionAwareService(): void
    {
        $instance = new PureInjectionAwareService('a');

        $this->assertEquals(
            "new oat\\tao\\model\\service\\PureInjectionAwareService('a')",
            $instance->__toPhpCode()
        );
    }

    public function testInjectedInjectionAwareService(): void
    {
        $instance = new HostInjectionAwareService(
            new PureInjectionAwareService('a')
        );

        $this->assertEquals(
            "new oat\\tao\\model\\service\\HostInjectionAwareService("
                . "new oat\\tao\\model\\service\\PureInjectionAwareService('a'))",
            $instance->__toPhpCode()
        );
    }

    public function testPureConfigurableAwareService(): void
    {
        $instance = new PureConfigurableService();

        $this->assertEquals(
            'new oat\tao\model\service\PureConfigurableService()',
            $instance->__toPhpCode()
        );
    }

    public function testHostPureConfigurableService(): void
    {
        $instance = new HostPureConfigurableService(
            new PureConfigurableService()
        );

        // phpcs:disable Generic.Files.LineLength
        $expected = <<<'EXPECTED'
new class implements \oat\oatbox\service\ServiceFactoryInterface {
    public function __invoke(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        return new oat\tao\model\service\HostPureConfigurableService(new oat\tao\model\service\PureConfigurableService());
    }
}
EXPECTED;
        // phpcs:enable Generic.Files.LineLength

        $this->assertEquals($expected, $instance->__toPhpCode());
    }

    public function testHostMixService(): void
    {
        $instance = new HostMixService(
            new PureConfigurableServiceWithId(),
            new PureInjectionAwareService('a')
        );

        // phpcs:disable Generic.Files.LineLength
        $expected = <<<'EXPECTED'
new class implements \oat\oatbox\service\ServiceFactoryInterface {
    public function __invoke(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        return new oat\tao\model\service\HostMixService($serviceLocator->get(oat\tao\model\service\PureConfigurableServiceWithId::class),
new oat\tao\model\service\PureInjectionAwareService('a'));
    }
}
EXPECTED;
        // phpcs:enable Generic.Files.LineLength

        $this->assertEquals($expected, $instance->__toPhpCode());
    }

    public function testHostMixNestedService(): void
    {
        $instance = new HostMixNestedService(
            new PureConfigurableService(),
            new HostPureConfigurableService(
                new PureConfigurableService()
            ),
            new PureConfigurableServiceWithId()
        );

        $expected = <<<'EXPECTED'
new class implements \oat\oatbox\service\ServiceFactoryInterface {
    public function __invoke(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        return new oat\tao\model\service\HostMixNestedService(new oat\tao\model\service\PureConfigurableService(),
new oat\tao\model\service\HostPureConfigurableService(new oat\tao\model\service\PureConfigurableService()),
$serviceLocator->get(oat\tao\model\service\PureConfigurableServiceWithId::class));
    }
}
EXPECTED;

        $this->assertEquals($expected, $instance->__toPhpCode());
    }

    public function testHostHostPureConfigurableService(): void
    {
        $instance = new HostHostPureConfigurableService(
            new HostPureConfigurableService(
                new PureConfigurableService([1, 2, 3])
            )
        );

        // phpcs:disable Generic.Files.LineLength
        $expected = <<<'EXPECTED'
new class implements \oat\oatbox\service\ServiceFactoryInterface {
    public function __invoke(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        return new oat\tao\model\service\HostHostPureConfigurableService(new oat\tao\model\service\HostPureConfigurableService(new oat\tao\model\service\PureConfigurableService(array(
    1,
    2,
    3
))));
    }
}
EXPECTED;
        // phpcs:enable Generic.Files.LineLength

        $this->assertEquals($expected, $instance->__toPhpCode());
    }

    public function testEmptyConstructor(): void
    {
        $instance = new EmptyConstructorInjectionAwareService();

        $expected = 'new oat\tao\model\service\EmptyConstructorInjectionAwareService()';

        $this->assertEquals($expected, $instance->__toPhpCode());
    }

    public function testConstructorWithVariadicParameters(): void
    {
        $instance = new InjectionAwareServiceWithVariadicParameters(10, 'one', 'two', 'tree');

        $expected = <<<'EXPECTED'
new oat\tao\model\service\InjectionAwareServiceWithVariadicParameters(10,
'one',
'two',
'tree')
EXPECTED;

        $this->assertEquals($expected, $instance->__toPhpCode());
    }
}
