<?php

declare(strict_types=1);

return [
    'name' => 'foo',
    'label' => 'Foo',
    'description' => 'Sample ext',
    'license' => 'GPL-2.0',
    'version' => '0.0.1',
    'author' => 'Open Assessment Technologies, CRP Henri Tudor',
    'requires' => [],
    'models' => [],
    'install' => [],
    'routes' => [
        '/foo' => 'oat\\tao\\test\\integration\\routing\\samples',
    ],
];
