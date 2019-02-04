<?php

namespace oat\tao\Model;

use oat\generis\Model\Console\ConsoleCommand;
use oat\generis\View\TestingSomething;

/**
 * Class HelloWorld
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class HelloWorld
{

    /**
     * @var ConsoleCommand
     */
    private $consoleCommand;
    /**
     * @var TestingSomething
     */
    private $diTest;

    /**
     * HelloWorld constructor.
     * @param ConsoleCommand $consoleCommand
     * @param TestingSomething $diTest
     */
    public function __construct(ConsoleCommand $consoleCommand, TestingSomething $diTest)
    {
        $this->consoleCommand = $consoleCommand;
        $this->diTest = $diTest;
    }

    /**
     *
     */
    public function runTest()
    {
        $this->consoleCommand->isEnabled();
        $this->diTest->testing();
    }
}