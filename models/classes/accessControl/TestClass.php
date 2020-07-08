<?php


namespace oat\tao\model\accessControl;


class TestClass
{
    private $a;

    public function __construct(int $a)
    {
        $this->a = $a;
    }

    public function add(int $b)
    {
        return $this->a + $b;
    }

    public function toString()
    {
        return (string)$this->a;
    }
}