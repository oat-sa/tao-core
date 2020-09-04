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
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\install\seed;

use oat\generis\test\TestCase;
use oat\tao\model\install\seed\SeedSorter;
use ReflectionException;

interface NoDepInterface {

}

class NoDepClass implements NoDepInterface
{
}

class NoDepNotInConfigClass
{
}

class DepFromServiceInConfigClass
{
    public function __construct(NoDepClass $class)
    {
    }
}

class DepFromInterfaceInConfigClass
{
    public function __construct(NoDepInterface $class)
    {
    }
}

class Dep2FromServiceInConfigClass
{
    public function __construct(DepFromServiceInConfigClass $class)
    {
    }
}

class DoubleDepFromServiceInConfigClass
{
    public function __construct(DepFromServiceInConfigClass $class, NoDepClass $class2)
    {
    }
}

class DoubleDepFromServiceInConfigAndNotInConfigClass
{
    public function __construct(NoDepNotInConfigClass $class, DoubleDepFromServiceInConfigClass $class2)
    {
    }
}

class DepFromServiceNotInConfigClass
{
    public function __construct(NoDepNotInConfigClass $class)
    {
    }
}

class NoDepArrayClass
{
    public function __construct(array $array)
    {
    }
}

class SeedSorterTest extends TestCase
{
    /** @var SeedSorter */
    private $sorter;

    protected function setUp(): void
    {
        $this->sorter = new SeedSorter();
    }

    /**
     * @throws ReflectionException
     */
    public function testSortConfigs(): void
    {
        $configConfiguration = [
            'DepFromInterfaceInConfigClass' => [
                'class' => DepFromInterfaceInConfigClass::class,
            ],
            'DoubleDepFromServiceInConfigAndNotInConfigClass' => [
                'class' => DoubleDepFromServiceInConfigAndNotInConfigClass::class,
            ],
            'DepFromServiceNotInConfigClass'                  => [
                'class' => DepFromServiceNotInConfigClass::class,
            ],
            'Dep2FromServiceInConfigClass'                    => [
                'class' => Dep2FromServiceInConfigClass::class,
            ],
            'DoubleDepFromServiceInConfigClass'               => [
                'class' => DoubleDepFromServiceInConfigClass::class,
            ],
            'DepFromServiceInConfigClass'                     => [
                'class' => DepFromServiceInConfigClass::class,
            ],
            'NoDepArrayClass'                                 => [
                'class' => NoDepArrayClass::class,
            ],
            'NoDepClass'                                      => [
                'class' => NoDepClass::class,
            ],
            'NotAClass'                                       => [
                'some' => 'config',
            ],
        ];

        $sortedConfigs = $this->sorter->sort($configConfiguration);

        $this->assertKeysAfter(['NoDepClass'], 'DepFromServiceInConfigClass', $sortedConfigs);
        $this->assertKeysAfter(['NoDepClass'], 'DepFromInterfaceInConfigClass', $sortedConfigs);
        $this->assertKeysAfter(
            ['DoubleDepFromServiceInConfigClass'],
            'DoubleDepFromServiceInConfigAndNotInConfigClass',
            $sortedConfigs
        );
        $this->assertKeysAfter(
            ['NoDepClass', 'DepFromServiceInConfigClass'],
            'DoubleDepFromServiceInConfigClass',
            $sortedConfigs
        );

        self::assertArrayHasKey('NoDepClass', $sortedConfigs);
        self::assertArrayHasKey('NoDepArrayClass', $sortedConfigs);
        self::assertArrayHasKey('DepFromServiceNotInConfigClass', $sortedConfigs);
        self::assertArrayHasKey('NotAClass', $sortedConfigs);
    }

    private function assertKeysAfter(array $priorKeys, string $needleKey, array $array): void
    {
        $maxKeyIndex = 0;

        $arrayKeys = array_keys($array);

        foreach ($priorKeys as $priorKey) {
            $keyIndex = array_search($priorKey, $arrayKeys, true);

            self::assertIsInt($keyIndex);

            if ($maxKeyIndex < $keyIndex) {
                $maxKeyIndex = $keyIndex;
            }
        }

        self::assertTrue($maxKeyIndex < array_search($needleKey, $arrayKeys, true));
    }
}
