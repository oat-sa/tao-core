<?php

namespace oat\tao\model\IdentifierGenerator\Generator;

class NumericIdentifierGenerator implements IdentifierGeneratorInterface
{
    /**
     * This will return 9 digits numeric identifier base on time and random number
     * i.e: 123456789
     */
    public function generate(array $options = []): string
    {
        return substr((string) floor(time() / 1000), 0, 7)
            . substr((string) floor(mt_rand(10, 100)), 0, 2);
    }
}
