<?php
/**
 * Created by PhpStorm.
 * User: sergii
 * Date: 27.02.18
 * Time: 10:08
 */

namespace oat\tao\scripts\update;


use common_ext_ExtensionsManager;
use common_ext_ExtensionUninstaller;
use oat\oatbox\extension\AbstractAction;

class TestUninstaller extends AbstractAction
{
    public function __invoke($params)
    {
        $extensionId = 'taoLti';

        if (common_ext_ExtensionsManager::singleton()->isInstalled($extensionId)) {
            $extension = common_ext_ExtensionsManager::singleton()->getExtensionById($extensionId);
            $installer = new common_ext_ExtensionUninstaller($extension);
            $installer->uninstall();
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Ok, cowboy');
    }
}
