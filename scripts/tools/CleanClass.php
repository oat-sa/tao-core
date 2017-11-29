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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA
 *
 */
namespace oat\tao\scripts\tools;

use oat\oatbox\extension\AbstractAction;

/**
 * This post-installation script creates a new local file source for services
 */
class CleanClass extends AbstractAction
{

    /**
     * @var \core_kernel_classes_Class
     */
    protected $class = null;

    /**
     * @var \tao_models_classes_ClassService
     */
    protected $service = null;

    public function __invoke($params)
    {
        $report = $this->verifyParams($params);

        if($report->getType() === \common_report_Report::TYPE_ERROR){
            return $report;
        }

        $resourceDeleted = $this->class->countInstances(array(), array('recursive' => true));

        if ($this->class->equals($this->service->getRootClass())) {
            foreach ($this->service->getRootClass()->getSubClasses() as $subClass){
                if(!$this->service->deleteClass($subClass)){
                    return \common_report_Report::createFailure('Error occured during deletion of class : '.$subClass->getUri());
                }
            }

            $instances = $this->class->getInstances();
            foreach ($instances as $instance) {
                if(!$this->service->deleteResource($instance)){
                    return \common_report_Report::createFailure('Error occured during deletion of resource : '.$instance->getUri());
                }
            }

        } else {
            if(!$this->service->deleteClass($this->class)){
                return \common_report_Report::createFailure('Error occured during deletion of class : '.$this->class->getUri());
            }
        }

        $report->setMessage(
            'All classes and instances under : ' . $this->class->getUri() . ' have been removed. ' .
            $resourceDeleted . ' resource(s) removed'
        );
        return $report;
    }

    protected function verifyParams($params){
        $this->finalReport = new \common_report_Report(\common_report_Report::TYPE_SUCCESS);

        if (isset($params[0])) {
            $serviceName = $params[0];
            if(is_a($serviceName, \tao_models_classes_ClassService::class, true)){
                $this->service = call_user_func([$serviceName, 'singleton'], []);
            } else {
                return new \common_report_Report(\common_report_Report::TYPE_ERROR, __('USAGE: please provide a valid service name as first parameter'));
            }
        } else {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, __('USAGE: please provide the service name as first parameter'));
        }

        if (isset($params[1])) {
            $extensionId = $params[1];

            /** @var \common_ext_ExtensionsManager $extensionManager */
            $extensionManager = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID);
            try{
                $extensionManager->getExtensionById($extensionId);
            } catch(\common_ext_ManifestNotFoundException $e){
                return new \common_report_Report(\common_report_Report::TYPE_ERROR, __('USAGE: please provide a valid extension id as second parameter'));
            }

        } else {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, __('USAGE: please provide a valid extension id as second parameter'));
        }

        $class_uri = null;
        if (isset($params[2])) {
            $class_uri = $params[2];
        }


        $rootClass = $this->service->getRootClass();
        if(is_null($class_uri)){
            $class = $rootClass;
        } else{
            $class = new \core_kernel_classes_Class($class_uri);
            if(!$class->isSubClassOf($rootClass)){
                $msg = "Usage: php index.php '" . __CLASS__ . "' [CLASS_URI]" . PHP_EOL;
                $msg .= "CLASS_URI : a valid test class uri". PHP_EOL . PHP_EOL;
                $msg .= "Uri : " . $class_uri . " is not a valid test class" . PHP_EOL;
                return \common_report_Report::createFailure($msg);
            }
        }
        $this->class = $class;

        return \common_report_Report::createSuccess('Valid parameters');
    }
}
