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

use oat\tao\model\mvc\Application\Resolution;
use oat\tao\model\mvc\psr7\Controller;
use oat\tao\model\mvc\psr7\Resolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * execute tao controller
 * Class TaoResolver
 * @package oat\tao\model\mvc\middleware
 */
class TaoControllerExecution extends AbstractTaoMiddleware
{


    /**
     * @param \tao_actions_CommonModule $controller
     * @param Resolution $resolution
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param $params
     * @return ResponseInterface $response
     * @throws \ActionEnforcingException
     */
    protected function getControllerResponse(\tao_actions_CommonModule $controller , Resolution $resolution , ServerRequestInterface $request, ResponseInterface $response , $params) {
        $params['request'] = $request;
        $params['response'] = $response;
        $action = $resolution->getMethodName();

        if (method_exists($controller, $action)) {
            ob_start();

            $controller->setPsr7($request,  $response);
            $controller->setResolution($resolution);
            $controller->setServiceLocator($this->getServiceLocator());
            $controllerResponse = call_user_func_array(array($controller, $action), $params);

            if($controllerResponse instanceof ResponseInterface) {
                $response = $controllerResponse;
            }

            $implicitContent = trim(ob_get_contents());
            ob_clean();
            if(!empty($implicitContent)) {
                $response->getBody()->write($implicitContent);
            }
        } else {
            throw new \ActionEnforcingException("Unable to find the action '" . $action . "' in '" . $resolution->getControllerClass() . "'." ,
                $resolution->getControllerClass() ,
                $action);
        }

        return $response;
    }


    public function __invoke( $request,  $response,  $args)
    {

        $post = $request->getParsedBody();
        if(is_null($post)) {
            $post = [];
        }
        $params   = array_merge($request->getQueryParams() , $post);

        $response = $this->getControllerResponse($args['controller'] , $args['resolution'] , $request , $response , $params);
        return  $response;
    }



}