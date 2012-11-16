<?php
interface tao_install_services_CheckService{
	/**
	 * Build a common_configuration_Component from a given set of input data.
	 * 
	 * @param tao_install_services_Data $data
	 * @return common_configuration_Component
	 */
	public static function buildComponent(tao_install_services_Data $data);
	
	/**
	 * Build the tao_install_services_Data result corresponding to the check
	 * performed by the service.
	 * 
	 * @param tao_install_services_Data $data
	 * @param common_configuration_Report $report
	 * @param common_configuration_Component $component
	 * @return tao_install_services_Data
	 */
	public static function buildResult(tao_install_services_Data $data,
									   common_configuration_Report $report,
									   common_configuration_Component $component);
}
?>