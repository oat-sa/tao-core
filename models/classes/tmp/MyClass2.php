<?php

declare(strict_types=1);

namespace oat\tao\model\tmp;

class MyClass2 implements MyInterface
{
    /** @var MyClass */
    private $myClass;

    public function __construct(MyClass $myClass)
    {
        $this->myClass = $myClass;
    }

    public function run(): string
    {
        return sprintf('%s WORLD', $this->myClass->run());
    }
}
