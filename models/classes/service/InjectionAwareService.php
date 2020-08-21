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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\service;

use common_Utils;
use oat\oatbox\service\ConfigurableService;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

abstract class InjectionAwareService extends ConfigurableService
{
    /** @var bool */
    private $isChildItem = false;

    // to skip checking parent's constructor parameters inherited from Configurable::class
    public function __construct()
    {
        parent::__construct([]);
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     *
     * @throws ReflectionException
     */
    public function __toPhpCode(): string
    {
        $content = 'new %s(%s)';

        if (!$this->isChildItem && $this->isFactoryNeeded($this)) {
            $content = <<<'FACTORY'
new class implements \oat\oatbox\service\ServiceFactoryInterface {
    public function __invoke(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        return new %s(%s);
    }
}
FACTORY;
        }

        return sprintf(
            $content,
            static::class,
            implode(",\n", $this->getSerializedDependencies())
        );
    }

    /**
     * @return array
     *
     * @throws ReflectionException
     */
    private function getSerializedDependencies(): array
    {
        return array_map(
            [common_Utils::class, 'toPHPVariableString'],
            $this->getDependencies()
        );
    }

    /**
     * @param InjectionAwareService $service
     *
     * @return iterable
     * @throws ReflectionException
     */
    protected function iterateParameters(InjectionAwareService $service): iterable
    {
        $class = new ReflectionClass($service);
        $constructor = $class->getMethod('__construct');

        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();

            if (!$class->hasProperty($parameterName)) {
                $message = sprintf(
                    'Cannot find property "%s" in class %s. Please name properties exactly like constructor parameters, or overload %s',
                    $parameterName,
                    static::class,
                    __METHOD__
                );
                throw new RuntimeException($message);
            }

            $classProperty = $class->getProperty($parameterName);

            if ($classProperty->isPrivate() || $classProperty->isProtected()) {
                $classProperty->setAccessible(true);
            }

            $value = $classProperty->getValue($service);

            $parameter->isVariadic()
                ? yield from $value
                : yield $value;
        }
    }

    /**
     * @return array A list of dependencies to be injected in their order.
     * @throws ReflectionException
     */
    protected function getDependencies(): array
    {
        $dependencies = [];

        foreach ($this->iterateParameters($this) as $propertyValue) {
            if ($propertyValue instanceof ConfigurableService) {
                if ($this->hasConfig($propertyValue)) {
                    $propertyValue = new PhpCode(
                        sprintf('$serviceLocator->get(%s::class)', get_class($propertyValue))
                    );
                }
                if ($propertyValue instanceof self) {
                    $propertyValue->isChildItem = true;
                }
            }

            $dependencies[] = $propertyValue;
        }

        return $dependencies;
    }

    private function hasConfig(ConfigurableService $service): bool
    {
        $className = get_class($service);

        return defined("$className::SERVICE_ID");
    }

    /**
     * @param InjectionAwareService $service
     *
     * @return bool
     * @throws ReflectionException
     */
    protected function isFactoryNeeded(InjectionAwareService $service): bool
    {
        foreach ($this->iterateParameters($service) as $propertyValue) {
            if (!$propertyValue instanceof ConfigurableService) {
                continue;
            }

            if (
                !$propertyValue instanceof self
                || $this->hasConfig($propertyValue)
            ) {
                return true;
            }

            $result = $this->isFactoryNeeded($propertyValue);
            if ($result) {
                return true;
            }
        }

        return false;
    }
}
