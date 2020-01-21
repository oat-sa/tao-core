<?php

namespace oat\tao\scripts\tools;

use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\di\ContainerMaintainer;

class ContainerBuilder extends ScriptAction
{

    protected function showTime()
    {
        return true;
    }

    protected function provideOptions()
    {
        return [
            'configuration' => [
                'prefix' => 'c',
                'longPrefix' => 'configuration',
                'required' => false,
                'defaultValue' => 'config/generis.conf.php',
                'description' => 'Path to the generis.conf.php relative to project root'
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'DI container Warmer';
    }

    /**
     * @inheritDoc
     */
    protected function run()
    {
        $containerMaintainer = new ContainerMaintainer();
        $containerMaintainer->buildConfiguration($this->getOption('configuration'), true);
        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Container Warmed');
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }

}
