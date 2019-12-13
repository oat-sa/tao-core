<?php

namespace oat\tao\controller\entry;

use oat\tao\model\DIAwareInterface;

class DummyStatic extends \tao_actions_CommonModule implements DIAwareInterface
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
