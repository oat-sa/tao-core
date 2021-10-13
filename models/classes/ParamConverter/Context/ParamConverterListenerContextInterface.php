<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Context;

use oat\tao\model\HttpFoundation\Request\RequestInterface;

interface ParamConverterListenerContextInterface
{
    public function getRequest(): RequestInterface;

    public function getController(): string;

    public function getMethod(): string;
}
