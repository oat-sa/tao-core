<?php

namespace oat\tao\scripts\tools\maintenance;

use oat\oatbox\action\Action;
use oat\tao\model\maintenance\Maintenance;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class Enable implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function __invoke($params)
    {
        try {
            if ($this->getMaintenanceService()->isApplicationEnabled()) {
                return \common_report_Report::createSuccess(__('TAO platform is already on live mode.'));
            }
            $this->getMaintenanceService()->setApplicationEnabled();
            return \common_report_Report::createSuccess(__('TAO platform is now live.'));
        } catch (\common_Exception $e) {
            return \common_report_Report::createFailure(__('Error: %s', $e->getMessage()));
        }
    }

    /**
     * @return Maintenance
     */
    protected function getMaintenanceService()
    {
        return $this->getServiceLocator()->get(Maintenance::SERVICE_ID);
    }
}