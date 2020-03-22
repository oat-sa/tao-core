<?php

declare(strict_types=1);

namespace oat\tao\model\tmp;

class MyClass2 implements MyInterface
{
    /** @var MyClass */
    private $myClass;
    /**
     * @var MyClass3
     */
    private $myClass3;

    public function __construct(MyClass $myClass, MyClass3 $myClass3)
    {
        $this->myClass = $myClass;
        $this->myClass3 = $myClass3;
    }

    public function run(): string
    {
        return sprintf('%s WORLD', $this->myClass->run());
    }
}
