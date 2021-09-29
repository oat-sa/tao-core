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
 * Copyright (c) 2014-2021 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\routing;

use Context;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use oat\tao\model\Middleware\MiddlewareRequestHandler;
use ReflectionException;
use IExecutable;
use ActionEnforcingException;
use oat\tao\model\http\ResponseEmitter;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\tao\model\http\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use common_session_SessionManager;
use tao_models_classes_AccessDeniedException;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\accessControl\data\DataAccessControl;
use oat\tao\model\accessControl\data\PermissionException;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\oatbox\event\EventManager;
use oat\tao\model\event\BeforeAction;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\log\TaoLoggerAwareInterface;
use oat\tao\model\action\CommonModuleInterface;
use oat\tao\model\ParamConverter\Event\ParamConverterEvent;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use oat\tao\model\ParamConverter\EventListener\ParamConverterListener;
use oat\tao\model\ParamConverter\Context\ParamConverterListenerContext;

/**
 * @TODO ActionEnforcer class documentation.
 *
 * @author Jerome Bogaerts <jerome@taotesting.com>
 * @author Joel Bout <joel@taotesting.com>
 */
class ActionEnforcer implements IExecutable, ServiceManagerAwareInterface, TaoLoggerAwareInterface
{
    use ServiceManagerAwareTrait;
    use LoggerAwareTrait;

    private $extension;

    private $controllerClass;
    private $action;
    private $parameters;

    private $request;
    private $response;

    public function __construct($extensionId, $controller, $action, array $parameters)
    {
        $this->extension = $extensionId;
        $this->controllerClass = $controller;
        $this->action = $action;
        $this->parameters = $parameters;
    }

    protected function getExtensionId()
    {
        return $this->extension;
    }

    protected function getControllerClass()
    {
        return $this->controllerClass;
    }

    protected function getAction()
    {
        return $this->action;
    }

    protected function getParameters()
    {
        return $this->parameters;
    }

    protected function getController()
    {
        $controllerClass = $this->getControllerClass();
        if (!class_exists($controllerClass)) {
            throw new ActionEnforcingException('Controller "' . $controllerClass . '" could not be loaded.', $controllerClass, $this->getAction());
        }

        $controller = $this->getClassInstance($controllerClass);

        $this->propagate($controller);
        if ($controller instanceof Controller) {
            $controller->setRequest($this->getRequest());
            $controller->setResponse($this->getResponse());
        }
        if ($controller instanceof CommonModuleInterface) {
            $controller->initialize();
        }
        return $controller;
    }

    private function getClassInstance(string $className): object
    {
        $serviceId = defined("$className::SERVICE_ID")
            ? $className::SERVICE_ID
            : $className;

        return $this->getServiceLocator()->has($serviceId)
            ? $this->getServiceLocator()->get($serviceId)
            : $this->propagate(new $className);
    }

    protected function getRequest()
    {
        if (!$this->request) {
            $this->request = ServerRequest::fromGlobals();
        }

        return $this->request;
    }

    protected function getResponse()
    {
        if (!$this->response) {
            $this->response = new Response();
        }
        return $this->response;
    }

    /**
     * @throws PermissionException
     * @throws \common_exception_Error
     * @throws \common_exception_MissingParameter
     * @throws tao_models_classes_AccessDeniedException
     */
    protected function verifyAuthorization()
    {
        $user = common_session_SessionManager::getSession()->getUser();
        if (!AclProxy::hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters())) {
            $func  = new FuncProxy();
            $data  = new DataAccessControl();
            //now go into details to see which kind of permissions are not correct
            if (
                $func->hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters()) &&
                !$data->hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters())
            ) {
                throw new PermissionException($user->getIdentifier(), $this->getAction(), $this->getControllerClass(), $this->getExtensionId());
            }

            throw new tao_models_classes_AccessDeniedException($user->getIdentifier(), $this->getAction(), $this->getControllerClass(), $this->getExtensionId());
        }
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->execute();
    }

    /**
     * @throws ActionEnforcingException
     * @throws ReflectionException
     * @throws \common_exception_Error
     * @throws \common_exception_MissingParameter
     * @throws tao_models_classes_AccessDeniedException
     */
    public function execute()
    {
        // Are we authorized to execute this action?
        try {
            $this->verifyAuthorization();
        } catch (PermissionException $pe) {
            //forward the action (yes it's an awful hack, but far better than adding a step in Bootstrap's dispatch error).
            Context::getInstance()->setExtensionName('tao');
            $this->action       = 'denied';
            $this->controllerClass   = 'tao_actions_Permission';
            $this->extension    = 'tao';
        }

        $response = $this->resolve($this->getRequest());

        $emitter = new ResponseEmitter();
        $emitter($response);
    }

    /**
     * @throws ActionEnforcingException
     * @throws ReflectionException
     * @throws \common_exception_Error
     */
    public function resolve(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        /** @var ControllerService $controllerService */
        $controllerService = $this->getServiceLocator()->get(ControllerService::SERVICE_ID);
        try {
            $controllerService->checkController($this->getControllerClass());
            $action = $controllerService->getAction($this->getControllerClass(), $this->getAction());
        } catch (RouterException $e) {
            throw new ActionEnforcingException($e->getMessage(), $this->getControllerClass(), $this->getAction());
        }

        $this->response = $this->getMiddlewareRequestHandler()->withOriginalResponse($this->getResponse())->handle(
            $request
        );

        $controller = $this->getController();

        if (!method_exists($controller, $action)) {
            throw new ActionEnforcingException(
                "Unable to find the action '" . $action . "' in '" . get_class($controller) . "'.",
                $this->getControllerClass(),
                $this->getAction()
            );
        }

        $actionParameters = $this->resolveParameters($request, $controller, $action);

        // Action method is invoked, passing request parameters as method parameters.
        $user = common_session_SessionManager::getSession()->getUser();
        $this->logDebug('Invoking ' . get_class($controller) . '::' . $action . ' by ' . $user->getIdentifier(), ['GENERIS', 'CLEARRFW']);

        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->trigger(new BeforeAction());

        $response = call_user_func_array([$controller, $action], $actionParameters);

        return $response instanceof ResponseInterface ? $response : $controller->getPsrResponse();
    }

    /**
     * @throws ReflectionException
     */
    private function resolveParameters(ServerRequestInterface $request, $controller, string $action): array
    {
        // search parameters method
        $reflect = new ReflectionMethod($controller, $action);
        $parameters = $this->getParameters();
        $actionParameters = [];

        // TODO make cleaner
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getMethodAnnotations($reflect);
        $paramConverterConfigurations = [];

        foreach ($annotations as $annotation) {
            if ($annotation instanceof ParamConverter) {
                $paramConverterConfigurations[] = $annotation;
            }
        }

        if (!empty($paramConverterConfigurations)) {
            $symfonyRequest = (new HttpFoundationFactory())->createRequest($request);
            $symfonyRequest->attributes->set(
                ParamConverterListener::REQUEST_ATTRIBUTE_CONVERTERS,
                $paramConverterConfigurations
            );

            $this->getEventManager()->trigger(
                new ParamConverterEvent(
                    new ParamConverterListenerContext([
                        ParamConverterListenerContext::PARAM_REQUEST => $symfonyRequest,
                        ParamConverterListenerContext::PARAM_CONTROLLER => $controller,
                        ParamConverterListenerContext::PARAM_METHOD => $action,
                    ])
                )
            );

            $convertedParameters = $symfonyRequest->attributes->all();
            unset($convertedParameters[ParamConverterListener::REQUEST_ATTRIBUTE_CONVERTERS]);

            $parameters = array_merge($parameters, $convertedParameters);
        }

        foreach ($reflect->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();
            $paramTypeName = $paramType !== null ? $paramType->getName() : null;

            if (isset($parameters[$paramName])) {
                $actionParameters[$paramName] = $parameters[$paramName];
            } elseif($paramTypeName === ServerRequest::class) {
                $actionParameters[$paramName] = $request;
            } elseif (class_exists($paramTypeName) || interface_exists($paramTypeName)) {
                $actionParameters[$paramName] = $this->getClassInstance($paramTypeName);
            } elseif (!$param->isDefaultValueAvailable()) {
                $this->logWarning('Missing parameter ' . $paramName . ' for ' . $this->getControllerClass() . '@' . $action);
            }
        }

        return $actionParameters;
    }

    private function getEventManager(): EventManager
    {
        return $this->getServiceLocator()->getContainer()->get(EventManager::SERVICE_ID);
    }

    private function getMiddlewareRequestHandler(): MiddlewareRequestHandler
    {
        return $this->getServiceManager()->get(MiddlewareRequestHandler::SERVICE_ID);
    }
}
