<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Request;

use Symfony\Component\HttpFoundation\Request;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;

interface ParamConverterInterface
{
    public function getPriority(): int;

    public function getName(): string;

    /**
     * Stores the object in the request.
     */
    public function apply(Request $request, ParamConverter $configuration): bool;

    /**
     * Checks if the object is supported.
     */
    public function supports(ParamConverter $configuration): bool;
}
