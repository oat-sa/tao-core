<?php
/*
 * This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */


namespace oat\tao\model\mvc\middleware;

use oat\tao\model\mvc\psr7\ActionExecutor;
use oat\tao\model\mvc\psr7\Controller;
use oat\tao\model\mvc\psr7\Resolver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\accessControl\data\DataAccessControl;
use oat\tao\model\accessControl\data\PermissionException;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use Psr\Http\Message\ServerRequestInterface;

/**
 * execute tao controller
 * Class TaoResolver
 * @package oat\tao\model\mvc\middleware
 */
class TaoControllerExecution extends AbstractTaoMiddleware
{

    protected $extension;

    protected $controller;

    protected $action;

    protected $parameters;

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

    protected function getController()
    {
        $controllerClass = $this->getControllerClass();
        if(class_exists($controllerClass)) {
            return new $controllerClass();
        } else {
            throw new \ActionEnforcingException('Controller "'.$controllerClass.'" could not be loaded.', $controllerClass, $this->getAction());
        }
    }

    protected function init($request) {
        /**
         * @var $resolver Resolver
         */
        $resolver = $this->container->get('resolver');

        $this->extension = $resolver->getExtensionId();
        $this->controller = $resolver->getControllerClass();
        $this->action = $resolver->getMethodName();

        $post = $request->getParsedBody();
        if(is_null($post)) {
            $post = [];
        }

        $params   = array_merge($request->getQueryParams() , $post);

        $this->parameters = $params;
    }

    protected function verifyAuthorization() {
        $user = \common_session_SessionManager::getSession()->getUser();
        if (!AclProxy::hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters())) {
            $func  = new FuncProxy();
            $data  = new DataAccessControl();
            //now go into details to see which kind of permissions are not correct
            if($func->hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters()) &&
                !$data->hasAccess($user, $this->getControllerClass(), $this->getAction(), $this->getParameters())){

                throw new PermissionException($user->getIdentifier(), $this->getAction(), $this->getControllerClass(), $this->getExtensionId());
            }

            throw new \tao_models_classes_AccessDeniedException($user->getIdentifier(), $this->getAction(), $this->getControllerClass(), $this->getExtensionId());
        }
    }

    /**
     * @param Resolver $resolver
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface $response
     * @throws \ActionEnforcingException
     */
    protected function getControllerResponse(Resolver $resolver , ServerRequestInterface $request, ResponseInterface $response) {
        $params['request'] = $request;
        $params['response'] = $response;
        $controllerClass = $resolver->getControllerClass();
        $action = $resolver->getMethodName();

        if (method_exists($controllerClass, $action)) {
            ob_start();
            $controller = new $controllerClass();

            if($controller instanceof Controller) {
                $controller->setPsr7($request,  $response);
            }
            $controller->setServiceLocator($this->getContainer()->get('taoService'));
            $controllerResponse = call_user_func_array(array($controller, $action), $params);

            if($controllerResponse instanceof ResponseInterface) {
                $response = $controllerResponse;
            }

            $implicitContent = ob_get_contents();
            ob_clean();
            $response = $this->response( $controller , $implicitContent , $response);
        } else {
            throw new \ActionEnforcingException("Unable to find the action '" . $action . "' in '" . $controllerClass . "'.",
                $controllerClass,
                $action);
        }
        return $response;
    }


    public function __invoke( $request,  $response,  $args)
    {
        /**
         * @var $resolver Resolver
         */

        $relativeUrl = $request->getAttribute('route')->getArgument('relativeUrl');

        $resolver = $this->container->get('resolver');

        $resolver->setRequest($request);
        $resolver->setRelativeUrl($relativeUrl);
        $this->init($request);
        $post = $request->getParsedBody();
        if(is_null($post)) {
            $post = [];
        }
        $params   = array_merge($request->getQueryParams() , $post);

        // Are we authorized to execute this action?
        try {

            $this->verifyAuthorization();

        } catch(\Exception $pe){

            $urlRouteService = $this->getContainer()->get('taoService')->get('tao/urlroute');
            if($request->hasHeader('X-Requested-With')) {
                return $response
                ->withStatus(403);
            } else {
                return $response
                    ->withStatus(302)
                    ->withHeader('Location', $urlRouteService->getLoginUrl($params));
            }

        }

        $response = $this->getControllerResponse($resolver , $request , $response);
        return  $this->convertHeaders($response);
    }

    protected function convertHeaders(ResponseInterface $response) {

        $headers = headers_list();

        foreach ($headers as $header) {
            list($name , $value) = explode(':' , $header);
            $response = $response->withAddedHeader($name , trim($value));

        }

        header_remove();
        return $response;
    }

    /**
     * @param $controller
     * @param $implicitContent
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function response($controller, $implicitContent ,ResponseInterface $response) {
        /**
         * @var $executor ActionExecutor
         */
        $executor = $this->getContainer()->get('taoService')->get(ActionExecutor::SERVICE_ID);

        $response = $executor->execute($controller , $implicitContent , $response);

        return $response;
    }

}