<?php
/**
 * Created by PhpStorm.
 * User: sergii
 * Date: 26.02.18
 * Time: 09:06
 */

namespace oat\tao\scripts\update;

use oat\oatbox\extension\AbstractAction;

class TestUpdater extends AbstractAction
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_exception_Error
     * @throws \common_exception_InconsistentData
     * @throws \common_exception_InvalidArgumentType
     * @throws \common_exception_MissingParameter
     * @throws \common_ext_ExtensionException
     * @throws \common_ext_InstallationException
     * @throws \common_ext_ManifestNotFoundException
     */
    public function __invoke($params)
    {
        $updater = new OntologyUpdater();

        $updater->syncModel('taoLti');
//        $updater->syncModel('taoDeliveryRdf');

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Ok, cowboy');
    }
}