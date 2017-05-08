<?php


return new \oat\tao\model\mvc\psr7\slimContainerFactory(
    [
        'context' => \oat\tao\model\mvc\psr7\Context::class,
        'resolver' => \oat\tao\model\mvc\psr7\Resolver::class,
    ]
);