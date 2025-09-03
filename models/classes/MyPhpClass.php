<?php

class MyPhpClass
{
    private $myProperty;

    public function contactParams(string $param1, string $param2, string $param3, string $param4, string $param5): string
    {
        $this->myProperty = "$param1, $param2, $param3, $param4, $param5";
        
        return $this->myProperty;
    }
}
