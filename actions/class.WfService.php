<?php
/*  
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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

/**
 * Base controller for interactive services of the workflow
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
abstract class tao_actions_WfService extends tao_actions_CommonModule {

	public function __construct() {
		tao_helpers_Context::load('STANDALONE_MODE');
		parent::__construct();
	}

	public function setView($identifier, $extensionID = null) {
		// override non AJAX calls for SAS
		if(tao_helpers_Request::isAjax()){
			parent::setView($identifier, $extensionID);
		} else {
			$view = self::getTemplatePath($identifier, $extensionID);
			$this->setData('includedView', $view);
			parent::setView('sas.tpl', 'tao');
		}
    }
    
    protected function setVariables($variables) {
    	
    	$ext	= common_ext_ExtensionsManager::singleton()->getExtensionById('wfEngine');
    	$loader = new common_ext_ExtensionLoader($ext);
    	$loader->load();
    	$variableService = wfEngine_models_classes_VariableService::singleton();

    	$cleaned = array();
    	foreach ($variables as $key => $value) {
    		$cleaned[$key] = (is_object($value) && $value instanceof core_kernel_classes_Resource) ? $value->getUri() : $value;
    	}
		return $variableService->save($cleaned);
    }

}
?>