<?php

class MyPhpClass
{
    private string $myProperty;

    public function contactParams(string $p1, string $p2, string $p3, string $p4, string $p5): string
    {
        $this->myProperty = "$p1, $p2, $p3, $p4, $p5";

        return $this->myProperty;
    }
}
