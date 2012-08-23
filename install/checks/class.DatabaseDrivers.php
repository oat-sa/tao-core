<?php
class tao_install_checks_DatabaseDrivers extends common_configuration_Component {
    
    public function check (){
        
        // One of these drivers must be found.
        $drivers = array('mysql',
                         'pgsql');
                         
        foreach ($drivers as $d){
            $dbCheck = new common_configuration_PHPDatabaseDriver(null, null, $d);
            $dbReport = $dbCheck->check();
            
            if ($dbReport->getStatus() == common_configuration_Report::VALID){
                return new common_configuration_Report($dbReport->getStatus(),
                                                       "A suitable Database Driver is available.",
                                                       $this);
            }
        }
        
        return new common_configuration_Report(common_configuration_Report::INVALID,
                                               "No suitable Database Driver detected. " .
                                               "Drivers supported by TAO are: " . implode(', ', $drivers) . '.',
                                               $this);
    }
}
?>