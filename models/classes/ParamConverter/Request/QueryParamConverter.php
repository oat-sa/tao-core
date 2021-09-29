<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Request;

use Symfony\Component\HttpFoundation\Request;

class QueryParamConverter extends AbstractParamConverter
{
    public function getName(): string
    {
        return 'oat.tao.param_converter.query';
    }

    protected function getData(Request $request, array $options): array
    {
        return $request->query->all();
    }
}
