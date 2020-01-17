<?php

namespace oat\tao\controller\entry;

class DummyStatic extends \tao_actions_CommonModule
{


    private $param;
    private $param2;

    public function __construct($veryImportantParam)
    {
        $this->param = $veryImportantParam;

    }

    public function test()
    {
        echo $this->param . PHP_EOL; //. $this->param2;
        die();

    }
}
