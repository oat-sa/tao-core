<?php

namespace oat\tao\model\accessControl;

class TestClass
{
    private $a;

    public function __construct(int $aa)
    {
        $this->a = $aa;
    }

    public function add(int $b)
    {
        return $this->a = $this->a + $b;
    }

    public function toString()
    {
        return (string)$this->a;
    }
}
