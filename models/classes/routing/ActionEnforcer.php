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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\tao\model\routing;

use GenerisActionEnforcer;
use IExecutable;
use ActionEnforcingException;
use ReflectionMethod;
use common_Logger;
use tao_models_classes_accessControl_AclProxy;

use common_session_SessionManager;
use tao_models_classes_AccessDeniedException;

/**
 * ActionEnforcer class
 * TODO ActionEnforcer class documentation.
 * 
 * @author J�r�me Bogaerts <jerome.bogaerts@tudor.lu> <jerome.bogaerts@gmail.com>
 */
class ActionEnforcer implements IExecutable
{	
    private $extension;
    
    private $controller;
    
    private $action;
    
    private $parameters;
    
    public function __construct($extensionId, $controller, $action, array $parameters) {
        $this->extension = $extensionId;
        $this->controller = $controller;
        $this->method = $action;
        $this->parameters = $parameters;
    }
    
    protected function getExtensionId() {
        return $this->extension;
    }
    
    protected function getControllerClass() {
        return $this->controller;
    }
    
    protected function getAction() {
        return $this->method;
    }
    
    protected function getParameters() {
        return $this->parameters;
    }
    
    protected function getController()
    {
        $controllerClass = $this->getControllerClass();
        if(class_exists($controllerClass)) {
            return new $controllerClass();
        } else {
            throw new ActionEnforcingException('Controller "'.$controllerClass.'" could not be loaded.', $controllerClass, $this->getAction());
        }
    }
    
    protected function verifyAuthorization() {
        if (preg_match('/([a-zA-Z]*)$/', $this->getControllerClass(), $matches) != 1) {
            throw new \common_exception_Error('Could not find shortname for ').$this->getControllerClass();
        }
        $shortName = $matches[0];
	    if (!tao_models_classes_accessControl_AclProxy::hasAccess($this->getAction(), $shortName, $this->getExtensionId(), $this->getParameters())) {
	        $userUri = common_session_SessionManager::getSession()->getUserUri();
	        throw new tao_models_classes_AccessDeniedException($userUri, $this->getAction(), $shortName, $this->getExtensionId());
	    }
    }
    
	public function execute()
	{
	    // Are we authorized to execute this action?
	    $this->verifyAuthorization();
	    
	    // get the controller
	    $controller = $this->getController();
	    $action = $this->getAction();
	     
	    // if the method related to the specified action exists, call it
	    if (method_exists($controller, $action)) {
	
	        // search parameters method
	        $reflect	= new ReflectionMethod($controller, $action);
	        $parameters	= $reflect->getParameters();
	
	        $tabParam 	= array();
	        foreach($reflect->getParameters() as $param)
	            $tabParam[$param->getName()] = $this->context->getRequest()->getParameter($param->getName());
	
	        // Action method is invoked, passing request parameters as
	        // method parameters.
	        common_Logger::d('Invoking '.get_class($controller).'::'.$action, ARRAY('GENERIS', 'CLEARRFW'));
	        call_user_func_array(array($controller, $action), $tabParam);
	
	        // Render the view if selected.
	        if ($controller->hasView())
	        {
	            $renderer = $controller->getRenderer();
	            echo $renderer->render();
	        }
	    }
	    else {
	        throw new ActionEnforcingException("Unable to find the action '".$action."' in '".get_class($controller)."'.",
	            $this->getControllerClass(),
	            $this->getAction());
	    }
	}

}