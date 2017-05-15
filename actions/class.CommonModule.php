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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

use oat\tao\helpers\Template;
use oat\tao\helpers\JavaScript;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\mvc\Application\ApplicationInterface;
use oat\tao\model\mvc\Application\TaoApplication;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \oat\tao\model\mvc\psr7\Controller\LegacyRequestTrait;
use \Zend\ServiceManager\ServiceLocatorAwareInterface;
use \Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Top level controller
 * All children extenions module should extends the CommonModule to access the shared data
 * deprecated please use oat\tao\model\mvc\psr7\Controller
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @deprecated since version v7.31.0
 *         
 */
abstract class tao_actions_CommonModule implements ServiceLocatorAwareInterface
{

    use LegacyRequestTrait;
    use ServiceLocatorAwareTrait;

    /**
     * The Modules access the models throught the service instance
     * 
     * @var tao_models_classes_Service
     */
    protected $service = null;

    /**
     * @var \oat\tao\model\mvc\Application\Resolution
     */
    protected $resolution;

    /**
     * empty constuctor
     */
    public function __construct()
    {
    }

    /**
     * @return \oat\tao\model\mvc\Application\Resolution
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * @param \oat\tao\model\mvc\Application\Resolution $resolution
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;
        return $this;
    }



    /**
     * Whenever or not the current user has access to a specific action
     * using functional and data access control
     *
     * @param string $controllerClass
     * @param string $action
     * @param array $parameters
     * @return boolean
     */
    public function hasAccess($controllerClass, $action, $parameters = [])
    {
        $user = common_session_SessionManager::getSession()->getUser();
        return AclProxy::hasAccess($user, $controllerClass, $action, $parameters);
    }

    /**
     *
     * @see Module::setView()
     * @param string $path
     *            view identifier
     * @param string $extensionID
     *            use the views in the specified extension instead of the current extension
     * @return $this
     */
    public function setView($path, $extensionID = null)
    {
        if(is_null($extensionID)) {
            $extensionID = $this->getResolution()->getExtensionId();
        }
        $this->getRenderer()->setTemplate(Template::getTemplate($path, $extensionID));
        return $this;
    }

    /**
     * Retrieve the data from the url and make the base initialization
     * 
     * @return void
     */
    protected function defaultData()
    {
        $context = Context::getInstance();
        
        $this->setData('extension', context::getInstance()->getExtensionName());
        $this->setData('module', $context->getModuleName());
        $this->setData('action', $context->getActionName());
        
        if ($this->hasRequestParameter('uri')) {
            
            // @todo stop using session to manage uri/classUri
            $this->setSessionAttribute('uri', $this->getRequestParameter('uri'));
            
            // inform the client of new classUri
            $this->setData('uri', $this->getRequestParameter('uri'));
        }
        if ($this->hasRequestParameter('classUri')) {
            
            // @todo stop using session to manage uri/classUri
            $this->setSessionAttribute('classUri', $this->getRequestParameter('classUri'));
            if (! $this->hasRequestParameter('uri')) {
                $this->removeSessionAttribute('uri');
            }
            
            // inform the client of new classUri
            $this->setData('uri', $this->getRequestParameter('classUri'));
        }
        
        if ($this->getRequestParameter('message')) {
            $this->setData('message', $this->getRequestParameter('message'));
        }
        if ($this->getRequestParameter('errorMessage')) {
            $this->setData('errorMessage', $this->getRequestParameter('errorMessage'));
        }

        $this->setData('client_timeout', $this->getClientTimeout());
        $this->setData('client_config_url', $this->getClientConfigUrl());
    }


    /**
     * Returns the absolute path to the specified template
     * 
     * @param string $identifier
     * @param string $extensionID
     * @return string
     */
    protected static function getTemplatePath($identifier, $extensionID = null)
    {
    	if ($extensionID === true) {
			$extensionID = 'tao';
			common_Logger::d('Deprecated use of setView() using a boolean');
		}
    	if(is_null($extensionID) || empty($extensionID)) {
    		$extensionID = Context::getInstance()->getExtensionName();
    	}
    	$ext = common_ext_ExtensionsManager::singleton()->getExtensionById($extensionID);
    	return $ext->getConstant('DIR_VIEWS').'templates'.DIRECTORY_SEPARATOR.$identifier;
    }
   
     
    /**
     * Helps you to add the URL of the client side config file
     * 
     * @param array $extraParameters additional parameters to append to the URL
     * @return string the URL
     */
    protected function getClientConfigUrl($extraParameters = []){
        return JavaScript::getClientConfigUrl($extraParameters);
    }


    /**
     * Get the client timeout value from the config.
     * 
     * @return int the timeout value in seconds
     */
    protected function getClientTimeout(){
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $config = $ext->getConfig('js');
        if($config != null && isset($config['timeout'])){
            return (int)$config['timeout'];
        } 
        return 30;
    }

    /**
     * Returns a report
     * 
     * @param common_report_Report $report
     */
    protected function returnReport(common_report_Report $report, $refresh = true) {
        if ($refresh) {
            $data = $report->getdata();
            if ($report->getType() == common_report_Report::TYPE_SUCCESS &&
                !is_null($data) && $data instanceof \core_kernel_classes_Resource) {
                $this->setData('message', $report->getMessage());
                $this->setData('selectNode', tao_helpers_Uri::encode($data->getUri()));
                $this->setData('reload', true);
                return $this->setView('form.tpl', 'tao');
            }
        }
        
        $this->setData('report', $report);
        $this->setView('report.tpl', 'tao');
    }

    public function setPsr7(ServerRequestInterface $request, ResponseInterface $response) {
        $this->request = new \oat\tao\model\mvc\psr7\clearfw\Request();
        $this->request->setPsrRequest($request);

        $this->response = new \oat\tao\model\mvc\psr7\clearfw\Response();
        $this->response->setPsrResponse($response);

        return $this;
    }

    /**
     *
     * @return ServerRequestInterface
     */
    public function getPsrRequest() {
        return $this->getRequest()->getPsrRequest();
    }

    /**
     *
     * @return ResponseInterface
     */
    public function getPsrResponse() {
        return $this->getResponse()->getPsrResponse();
    }

    /**
     * @param $response ResponseInterface
     * @return $this
     */
    public function updateResponse(ResponseInterface $response) {
        $this->getResponse()->setPsrResponse($response);
        return $this;
    }

    /**
     * write
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function sendResponse(ResponseInterface $response = null) {

        if($this->hasView()) {
            $view = $this->getRenderer()->render();
            $response->getBody()->write($view);
        }
        $this->updateResponse($response);
        return $response;
    }

    /**
     * Forward create new request params and forward
     * @param $action
     * @param null $controller
     * @param null $extension
     * @param array $params
     * @return ResponseInterface
     */
    public function forward($action, $controller = null, $extension = null, $params = array())
    {

        $url = \tao_helpers_Uri::url($action, $controller , $extension );

        return $this->forwardUrl($url);
    }

    /**
     * Forward using the TAO FlowController implementation
     * @param string $url
     * @return ResponseInterface
     */
    public function forwardUrl($url)
    {
        return $this->executeForward($url);
    }

    /**
     * execute forward using controller execution middleware
     * @param string $url
     * @return ResponseInterface
     */
    protected function executeForward($url) {
        /**
         * @var $application TaoApplication
         */
        $application = $this->getServiceManager()->get(ApplicationInterface::SERVICE_ID);
        $response = $application->forward($url);
        $this->updateResponse($response);
        return $this->getPsrResponse();
    }

    /**
     * Redirect using the TAO FlowController implementation
     * @param $url
     * @param int $statusCode
     * @return void
     */
    public function redirect($url, $statusCode = 302)
    {
        \common_Logger::i(__METHOD__);
        $response = $this->getPsrResponse()->withStatus($statusCode)->withHeader('Location' , $url);
        $this->updateResponse($response);
        \common_Logger::i('redirect = ' . $url);
        /**
         * @var $application TaoApplication
         */
        $application = $this->getServiceManager()->get(ApplicationInterface::SERVICE_ID);
        $application->finalise($response)->end();
    }

    protected function returnJson($data, $httpStatus = 200)
    {
        $response = $this->getPsrResponse();
        $response =  $response->withStatus($httpStatus)->withHeader('Content-Type' , 'application/json');
        $response->getBody()->write(json_encode($data));
        $this->updateResponse($response);
        return $response;
    }

    public function getRenderer() {
        if (!isset($this->renderer)) {
            $this->renderer = new Renderer();
        }
        return $this->renderer;
    }

    /**
     * @deprecated since 10.0.0
     * @param string $description
     * @param bool $returnLink
     * @param null $httpStatus
     * @throws \common_exception_Error
     */
    protected function returnError($description, $returnLink = true, $httpStatus = null)
    {
        throw new \common_exception_Error($description);
    }

    public function setData($key, $value)
    {
        $this->getRenderer()->setData($key, $value);
    }

    public function hasView() {
        return isset($this->renderer) && $this->renderer->hasTemplate();
    }

    protected function getServiceManager()
    {
        return $this->getServiceLocator();
    }
}
