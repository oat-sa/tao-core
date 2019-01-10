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

use IExecutable;
use ActionEnforcingException;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\oatbox\service\ServiceManagerAwareTrait;
use ReflectionClass;
use ReflectionMethod;

use common_session_SessionManager;
use tao_models_classes_AccessDeniedException;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\accessControl\data\DataAccessControl;
use oat\tao\model\accessControl\data\PermissionException;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\event\EventManager;
use oat\tao\model\event\BeforeAction;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\log\TaoLoggerAwareInterface;

/**
 * ActionEnforcer class
 * TODO ActionEnforcer class documentation.
 * 
 * @author Jerome Bogaerts <jerome@taotesting.com>
 * @author Joel Bout <joel@taotesting.com>
 */
class ActionEnforcer implements IExecutable, ServiceManagerAwareInterface, TaoLoggerAwareInterface
{
    use ServiceManagerAwareTrait;
    use LoggerAwareTrait;

    private $extension;
    
    private $controller;
    
    private $action;
    
    private $parameters;
    
    public function __construct($extensionId, $controller, $action, array $parameters) {
        $this->extension = $extensionId;
        $this->controller = $controller;
        $this->action = $action;
        $this->parameters = $parameters;
    }
    
    protected function getExtensionId() {
        return $this->extension;
    }
    
    protected function getControllerClass() {
        return $this->controller;
    }
    
    protected function getAction() {
        return $this->action;
    }
    
    protected function getParameters() {
        return $this->parameters;
    }

    /**
     * @throws PermissionException
     * @throws \common_exception_Error
     * @throws \common_exception_MissingParameter
     * @throws tao_models_classes_AccessDeniedException
     */
    protected function verifyAuthorization() {
        $user = common_session_SessionManager::getSession()->getUser();
        if (!AclProxy::hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters())) {
            $func  = new FuncProxy();
            $data  = new DataAccessControl();
            //now go into details to see which kind of permissions are not correct
            if($func->hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters()) && 
               !$data->hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters())){
               
                throw new PermissionException($user->getIdentifier(), $this->getAction(), $this->getControllerClass(), $this->getExtensionId());
            } 

            throw new tao_models_classes_AccessDeniedException($user->getIdentifier(), $this->getAction(), $this->getControllerClass(), $this->getExtensionId());
        }
    }

    /**
     * @throws ActionEnforcingException
     * @throws \ReflectionException
     * @throws \common_exception_Error
     * @throws \common_exception_MissingParameter
     * @throws tao_models_classes_AccessDeniedException
     */
    public function execute()
    {
        /** @var ControllerService $controllerService */
        $controllerService = $this->getServiceLocator()->get(ControllerService::SERVICE_ID);
        try {
            $controller = $controllerService->getController($this->getControllerClass());
            $action = $controllerService->getAction($this->getAction());
        } catch (RouterException $e) {
            throw new ActionEnforcingException($e->getMessage(), $this->getControllerClass(), $this->getAction());
        }

        // Are we authorized to execute this action?
        try {
            $this->verifyAuthorization();

            // search parameters method
            $reflect	= new ReflectionMethod($controller, $action);
            $parameters	= $this->getParameters();

            $tabParam 	= array();
            foreach($reflect->getParameters() as $param) {
                if (isset($parameters[$param->getName()])) {
                    $tabParam[$param->getName()] = $parameters[$param->getName()];
                } elseif (!$param->isDefaultValueAvailable()) {
                    $this->logWarning('Missing parameter '.$param->getName().' for '.$this->getControllerClass().'@'.$action);
                }
            }

            // Action method is invoked, passing request parameters as
            // method parameters.
            $user = common_session_SessionManager::getSession()->getUser();
            $this->logDebug('Invoking '.get_class($controller).'::'.$action.' by '.$user->getIdentifier(), ARRAY('GENERIS', 'CLEARRFW'));

            $eventManager = ServiceManager::getServiceManager()->get(EventManager::CONFIG_ID);
            $eventManager->trigger(new BeforeAction());

            call_user_func_array(array($controller, $action), $tabParam);

            // Render the view if selected.
            if ($controller->hasView())
            {
                $renderer = $controller->getRenderer();
                echo $renderer->render();
            }
        } catch(PermissionException $pe){
            //forward the action (yes it's an awful hack, but far better than adding a step in Bootstrap's dispatch error).
            \Context::getInstance()->setExtensionName('tao');
            $this->action       = 'denied';
            $this->controller   = 'tao_actions_Permission';
            $this->extension    = 'tao';
        }
    }

}