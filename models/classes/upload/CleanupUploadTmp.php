<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\upload;

use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\action\Action;

/**
 * Remove all stored at tmp space files
 *
 */
class CleanupUploadTmp extends ConfigurableService implements Action
{
    /**
     * @param array $params
     * @return \common_report_Report
     */
    public function __invoke($params)
    {

        /** @var UploadService $uploadService */
        $tmpSpaceFlySystemId = $this->getServiceManager()->get(UploadService::SERVICE_ID)->getUploadFSid();

        $fs = $this->getServiceManager()->get(FileSystemService::SERVICE_ID)->getFileSystem($tmpSpaceFlySystemId);
        $report = new \common_report_Report(\common_report_Report::TYPE_INFO, __('Cleaning up tmp space started'));
        $files = 0;
        $dirs = 0;
        foreach ($fs->listContents() as $fileInfo) {
            if ('file' === $fileInfo['type']) {
                $file = new File($tmpSpaceFlySystemId, $fileInfo['path']);
                $file->setServiceLocator($this->getServiceManager());
                $file->delete();
                $files++;
            }
            if ('dir' === $fileInfo['type']) {
                $dir = new Directory($tmpSpaceFlySystemId, $fileInfo['path']);
                $dir->setServiceLocator($this->getServiceManager());
                $dir->deleteSelf();
                $dirs++;
            }

            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS,
                __('Removing: %s', $fileInfo['path'])));
        }

        $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Total:')));
        $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Removed %s files', $files)));
        $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS,
            __('Removed %s directories', $dirs)));

        $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS,
            __('Cleaning up tmp space complete')));

        return $report;
    }

}
