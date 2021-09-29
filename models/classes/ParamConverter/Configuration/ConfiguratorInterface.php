<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Configuration;

use ReflectionFunctionAbstract;
use Symfony\Component\HttpFoundation\Request;

interface ConfiguratorInterface
{
    /**
     * Apply conversion for non-configured objects
     *
     * @param ParamConverter[] $configurations
     */
    public function configure(ReflectionFunctionAbstract $reflection, Request $request, array &$configurations): void;
}
