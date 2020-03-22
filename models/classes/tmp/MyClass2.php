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
    /**
     * @var ClassWithServiceLocator
     */
    private $classWithServiceLocator;

    public function __construct(MyClass $myClass, MyClass3 $myClass3, ClassWithServiceLocator $classWithServiceLocator)
    {
        $this->myClass = $myClass;
        $this->myClass3 = $myClass3;
        $this->classWithServiceLocator = $classWithServiceLocator;
    }

    public function run(): string
    {
        $this->classWithServiceLocator->getSession()->getCurrentUser();

        return sprintf('%s WORLD', $this->myClass->run());
    }
}
