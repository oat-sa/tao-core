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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg
 *                         (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2025 (update and modification) Open Assessment Technologies SA;
 */

use oat\oatbox\user\User;
use oat\tao\model\http\LegacyController;
use oat\tao\helpers\LegacySessionUtils;
use oat\tao\model\action\CommonModuleInterface;
use oat\tao\model\mvc\RendererTrait;
use oat\tao\model\security\ActionProtector;
use oat\tao\helpers\Template;
use oat\tao\helpers\JavaScript;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\accessControl\AclProxy;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\tao\model\accessControl\ActionAccessControl;
use oat\tao\model\accessControl\Context as AclContext;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\security\xsrf\TokenService;
use Zend\ServiceManager\ServiceLocatorInterface;

use function GuzzleHttp\Psr7\stream_for;

/**
 * Top level controller
 * All children extensions module should extends the CommonModule to access the shared data
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2 http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 *
 */
abstract class tao_actions_CommonModule extends LegacyController implements
    ServiceManagerAwareInterface,
    CommonModuleInterface
{
    use ServiceManagerAwareTrait {
        getServiceManager as protected getOriginalServiceManager;
        getServiceLocator as protected getOriginalServiceLocator;
        setServiceLocator as protected setOriginalServiceLocator;
    }
    use LoggerAwareTrait;
    use RendererTrait {
        setView as protected setRendererView;
    }
    use LegacySessionUtils;

    /**
     * The Modules access the models through the service instance
     *
     * @var tao_models_classes_Service
     * @deprecated
     */
    protected $service;

    /**
     * tao_actions_CommonModule constructor.
     * @security("hide");
     */
    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        /** @var ActionProtector $actionProtector */
        $actionProtector = $this->getServiceLocator()->get(ActionProtector::SERVICE_ID);
        $actionProtector->setHeaders();
    }

    /**
     * Whenever or not the current user has access to a specific action
     * using functional and data access control
     *
     * @param string $controllerClass
     * @param string $action
     * @param array $parameters
     * @return boolean
     * @throws common_exception_Error
     */
    protected function hasAccess($controllerClass, $action, $parameters = [])
    {
        $user = $this->getSession()->getUser();
        return AclProxy::hasAccess($user, $controllerClass, $action, $parameters);
    }

    /**
     * @deprecated Use $this->hasWriteAccessByContext()
     */
    protected function hasWriteAccessToAction(string $action, ?User $user = null): bool
    {
        $context = new AclContext([
            AclContext::PARAM_CONTROLLER => static::class,
            AclContext::PARAM_ACTION => $action,
            AclContext::PARAM_USER => $user,
        ]);

        return $this->hasWriteAccessByContext($context);
    }

    protected function hasReadAccessByContext(AclContext $context): bool
    {
        return $this->getActionAccessControl()->contextHasReadAccess($context);
    }

    protected function hasWriteAccessByContext(AclContext $context): bool
    {
        return $this->getActionAccessControl()->contextHasWriteAccess($context);
    }

    protected function getUserRoles(): array
    {
        return $this->getSession()->getUser()->getRoles();
    }

    /**
     *
     * @see Module::setView()
     * @param string $path
     *            view identifier
     * @param string $extensionID
     *            use the views in the specified extension instead of the current extension
     */
    public function setView($path, $extensionID = null)
    {
        $this->setRendererView(Template::getTemplate($path, $extensionID));
    }

    /**
     * Retrieve the data from the url and make the base initialization
     *
     * @return void
     * @throws common_ext_ExtensionException
     */
    protected function defaultData()
    {
        $context = Context::getInstance();

        $this->setData('extension', $context->getExtensionName());
        $this->setData('module', $context->getModuleName());
        $this->setData('action', $context->getActionName());

        if ($this->hasRequestParameter('uri')) {
            // inform the client of new classUri
            $this->setData('uri', $this->getRequestParameter('uri'));
        }

        if ($this->hasRequestParameter('classUri')) {
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
     * Function to return an user readable error
     * Does not work with ajax Requests yet
     *
     * @param string $description error to show
     * @param boolean $returnLink whenever or not to add a return link
     * @param int $httpStatus
     * @throws common_Exception
     */
    protected function returnError($description, $returnLink = true, $httpStatus = null)
    {
        if ($this->isXmlHttpRequest()) {
            $this->logWarning('Called ' . __FUNCTION__ . ' in an unsupported AJAX context');
            throw new common_Exception($description);
        }
        $this->setData('message', $description);
        $this->setData('returnLink', $returnLink);
        if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) == parse_url(ROOT_URL, PHP_URL_HOST)) {
            $this->setData('returnUrl', htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES));
        } else {
            $this->setData('returnUrl', false);
        }
        if ($httpStatus !== null && file_exists(Template::getTemplate("error/error{$httpStatus}.tpl"))) {
            $this->setView("error/error{$httpStatus}.tpl", 'tao');
        } else {
            $this->setView('error/user_error.tpl', 'tao');
        }
    }

    /**
     * Returns the absolute path to the specified template
     *
     * @param string $identifier
     * @param string $extensionID
     * @return string
     * @throws common_exception_Error
     * @throws common_ext_ExtensionException
     */
    protected static function getTemplatePath($identifier, $extensionID = null)
    {
        if ($extensionID === true) {
            $extensionID = 'tao';
            common_Logger::d('Deprecated use of setView() using a boolean');
        }
        if ($extensionID === null) {
            $extensionID = Context::getInstance()->getExtensionName();
        }
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById($extensionID);
        return $ext->getConstant('DIR_VIEWS') . 'templates' . DIRECTORY_SEPARATOR . $identifier;
    }

    /**
     * Helps you to add the URL of the client side config file
     *
     * @param array $extraParameters additional parameters to append to the URL
     * @return string the URL
     */
    protected function getClientConfigUrl($extraParameters = [])
    {
        return JavaScript::getClientConfigUrl($extraParameters);
    }

    /**
     * Get the client timeout value from the config.
     *
     * @return int the timeout value in seconds
     * @throws common_ext_ExtensionException
     */
    protected function getClientTimeout()
    {
        $ext = $this->getServiceManager()->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('tao');
        $config = $ext->getConfig('js');
        if ($config !== null && isset($config['timeout'])) {
            return (int)$config['timeout'];
        }
        return 30;
    }

    /**
     * Return json response.
     *
     * @param array|\JsonSerializable $data
     * @param int $httpStatus
     *
     * @deprecated use \oat\tao\model\http\HttpJsonResponseTrait::setSuccessJsonResponse for standard response
     * @deprecated use \oat\tao\model\http\HttpJsonResponseTrait::setErrorJsonResponse for standard response
     */
    protected function returnJson($data, $httpStatus = 200)
    {
        header(HTTPToolkit::statusCodeHeader($httpStatus));
        Context::getInstance()->getResponse()->setContentHeader('application/json');
        $this->response = $this->getPsrResponse()->withBody(stream_for(json_encode($data)));
    }

    /**
     * Returns a report
     *
     * @param common_report_Report $report
     */
    protected function returnReport(common_report_Report $report)
    {
        $data = $report->getData();
        $successes = $report->getSuccesses();

        // if report has no data, try to get it from the sub report
        while ($data === null && count($successes) > 0) {
            $firstSubReport = current($successes);
            $data = $firstSubReport->getData();
            $successes = $firstSubReport->getSuccesses();
        }

        if ($data !== null && $data instanceof core_kernel_classes_Resource) {
            $this->setData('selectNode', tao_helpers_Uri::encode($data->getUri()));
        }
        $this->setData('report', $report);
        $this->setView('report.tpl', 'tao');
    }

    /**
     * Get the current session
     *
     * @return common_session_Session
     * @throws common_exception_Error
     */
    protected function getSession()
    {
        return common_session_SessionManager::getSession();
    }

    /**
     * Get the service Manager
     *
     * @deprecated Use $this->propagate or $this->registerService to access ServiceManager functionalities
     * @deprecated To get the service dependencies manager, use $this->getServiceLocator
     *
     * @return ServiceManager
     */
    protected function getServiceManager()
    {
        try {
            $serviceManager = $this->getOriginalServiceManager();
        } catch (InvalidServiceManagerException $e) {
            $serviceManager = ServiceManager::getServiceManager();
        }
        return $serviceManager;
    }

    /**
     * @return ServiceLocatorInterface
     * @security("hide");
     */
    public function getServiceLocator()
    {
        return $this->getOriginalServiceLocator();
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     * @security("hide");
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        return $this->setOriginalServiceLocator($serviceLocator);
    }

    /**
     * Validate a CSRF token, based on the CSRF header.
     *
     * @throws common_exception_Unauthorized
     */
    protected function validateCsrf()
    {
        if (!$this->getPsrRequest()->hasHeader(TokenService::CSRF_TOKEN_HEADER)) {
            $this->logCsrfFailure(sprintf('Missing %s header.', TokenService::CSRF_TOKEN_HEADER));
        }

        $csrfTokenHeader = $this->getPsrRequest()->getHeader(TokenService::CSRF_TOKEN_HEADER);
        $csrfToken = current($csrfTokenHeader);

        /** @var TokenService $tokenService */
        $tokenService = $this->getServiceLocator()->get(TokenService::SERVICE_ID);
        $newToken = null;

        try {
            if ($tokenService->validateToken($csrfToken)) {
                $newToken = $tokenService->createToken()->getValue();
            }
        } catch (common_exception_Unauthorized $e) {
            $this->logCsrfFailure($e->getMessage(), $csrfToken);
        }

        $this->response = $this->getPsrResponse()->withHeader(TokenService::CSRF_TOKEN_HEADER, $newToken);
    }

    /**
     * Logs a CSRF validation error
     *
     * @param string $exceptionMessage
     * @param null $token
     * @throws common_exception_Unauthorized
     */
    private function logCsrfFailure($exceptionMessage, $token = null)
    {
        try {
            $userIdentifier = $this->getSession()->getUser()->getIdentifier();
        } catch (common_exception_Error $e) {
            $this->logError('Unable to retrieve session! ' . $e->getMessage());
            throw new common_exception_Unauthorized($exceptionMessage);
        }

        $requestMethod  = $this->getPsrRequest()->getMethod();
        $requestUri     = $this->getPsrRequest()->getUri();
        $requestHeaders = $this->getHeaders();

        $this->logWarning(
            '[CSRF] - Failed to validate CSRF token. The following exception occurred: ' . $exceptionMessage
        );
        $this->logWarning(
            "[CSRF] \n" .
            "CSRF validation information: \n" .
            'Provided token: ' . ($token ?: 'none')  . " \n" .
            'User identifier: ' . $userIdentifier  . " \n" .
            'Request: [' . $requestMethod . '] ' . $requestUri   . " \n" .
            "Request Headers : \n" .
            urldecode(http_build_query($requestHeaders, '', "\n"))
        );

        throw new common_exception_Unauthorized($exceptionMessage);
    }

    /**
     * Ensure the template is rendered as part of the response
     * {@inheritDoc}
     * @see \oat\tao\model\http\Controller::getPsrResponse()
     */
    public function getPsrResponse()
    {
        $response = parent::getPsrResponse();
        return $this->hasView()
        ? $response->withBody(stream_for($this->getRenderer()->render()))
        : $response;
    }

    private function getActionAccessControl(): ActionAccessControl
    {
        return $this->getServiceLocator()->get(ActionAccessControl::SERVICE_ID);
    }
}
