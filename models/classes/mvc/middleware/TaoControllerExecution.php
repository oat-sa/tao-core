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

use oat\oatbox\service\ServiceManager;
use oat\tao\model\mvc\psr7\ActionExecutor;
use oat\tao\model\mvc\psr7\InterruptedActionException;
use oat\tao\model\mvc\psr7\Resolver;
use oat\tao\model\routing\ActionEnforcer;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

/**
 * execute tao controller
 * Class TaoResolver
 * @package oat\tao\model\mvc\middleware
 */
class TaoControllerExecution extends AbstractTaoMiddleware
{

    public function __invoke( $request,  $response,  $args)
    {
        /**
         * @var $resolver Resolver
         */


        $resolver = $this->container->get('resolver');
        $resolver->setRequest($request);

        try {
            $post = $request->getParsedBody();
            if(is_null($post)) {
                $post = [];
            }

            $params   = array_merge($request->getQueryParams() , $post);
            $params['request'] = $request;
            $params['response'] = $response;

            $controllerClass = $resolver->getControllerClass();
            $action = $resolver->getMethodName();
            if (method_exists($controllerClass, $action)) {
                ob_start();
                $controller = new $controllerClass();
                call_user_func_array(array($controller, $action), $params);
                $implicitContent = ob_get_contents();
                ob_clean();
                $response = $this->response( $controller , $implicitContent , $response);
            } else {
                throw new \ActionEnforcingException("Unable to find the action '" . $action . "' in '" . $controllerClass . "'.",
                    $controllerClass,
                    $action);
            }
        } catch (\InterruptedActionException $iE) {

        }

        return  $this->convertHeaders($response);
    }

    protected function convertHeaders(ResponseInterface $response) {

        $headers = headers_list();

        foreach ($headers as $header) {
            list($name , $value) = explode(':' , $header);
            $response = $response->withHeader($name , trim($value));

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