<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Manager;

use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;
use oat\tao\model\ParamConverter\Request\ParamConverterInterface;

class ParamConverterManager
{
    /** @var array */
    private $converters = [];

    /** @var array */
    private $namedConverters = [];

    /**
     * @param ParamConverterInterface[] $converters
     */
    public function __construct(array $converters = [])
    {
        foreach ($converters as $converter) {
            $this->add($converter, $converter->getPriority(), $converter->getName());
        }
    }

    /**
     * @param ParamConverter[] $configurations
     */
    public function apply(Request $request, array $configurations): void
    {
        foreach ($configurations as $configuration) {
            $this->applyConfiguration($request, $configuration);
        }
    }

    /**
     * Adds a parameter converter.
     *
     * Converters match either explicitly via $name or by iteration over all
     * converters with a $priority. If you pass a $priority = null then the
     * added converter will not be part of the iteration chain and can only
     * be invoked explicitly.
     */
    public function add(ParamConverterInterface $converter, ?int $priority = 0, string $name = null): void
    {
        if ($priority !== null) {
            if (!isset($this->converters[$priority])) {
                $this->converters[$priority] = [];
            }

            $this->converters[$priority][] = $converter;
        }

        if ($name !== null) {
            $this->namedConverters[$name] = $converter;
        }
    }

    /**
     * Returns all registered param converters.
     *
     * @return ParamConverterInterface[]
     */
    public function all(): array
    {
        krsort($this->converters);
        $converters = [];

        foreach ($this->converters as $all) {
            $converters = array_merge($converters, $all);
        }

        return $converters;
    }

    private function applyConfiguration(Request $request, ParamConverter $configuration): void
    {
        $value = $request->attributes->get($configuration->getName());
        $className = $configuration->getClass();

        // If the value is already an instance of the class we are trying to convert it into
        // we should continue as no conversion is required
        if (is_object($value) && $value instanceof $className) {
            return;
        }

        if ($converterName = $configuration->getConverter()) {
            $this->checkProvidedConverterName($converterName, $configuration->getName());
            /** @var ParamConverterInterface $converter */
            $converter = $this->namedConverters[$converterName];

            $this->checkConverterSupport($converter, $configuration);
            $converter->apply($request, $configuration);

            return;
        }

        foreach ($this->all() as $converter) {
            if ($converter->supports($configuration)) {
                if ($converter->apply($request, $configuration)) {
                    return;
                }
            }
        }
    }

    private function checkProvidedConverterName(string $converterName, string $parameter): void
    {
        if (!isset($this->namedConverters[$converterName])) {
            throw new RuntimeException(
                sprintf(
                    'No converter named "%s" found for conversion of parameter "%s".',
                    $converterName,
                    $parameter
                )
            );
        }
    }

    private function checkConverterSupport(
        ParamConverterInterface $converter,
        ParamConverter $configuration
    ): void {
        if (!$converter->supports($configuration)) {
            throw new RuntimeException(
                sprintf(
                    'Converter "%s" does not support conversion of parameter "%s".',
                    $converter->getName(),
                    $configuration->getName()
                )
            );
        }
    }
}
