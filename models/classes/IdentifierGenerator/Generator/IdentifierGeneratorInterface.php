<?php

namespace oat\tao\model\IdentifierGenerator\Generator;

interface IdentifierGeneratorInterface
{
    public const OPTION_RESOURCE = 'resource';
    public const OPTION_RESOURCE_ID = 'resource_id';

    public function generate(array $options = []): string;
}
