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
use oat\tao\model\mvc\psr7\InterruptedActionException;
use oat\tao\model\mvc\psr7\Resolver;
use oat\tao\model\routing\ActionEnforcer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
            ob_start();
            $params   = array_merge($request->getParsedBody() , $request->getQueryParams());
            $enforcer = new ActionEnforcer($resolver->getExtensionId(), $resolver->getControllerClass(), $resolver->getMethodName(), $params);
            //$controller = $enforcer->execute();
            //$implicitContent = ob_get_clean();
            return $response;
        } catch (InterruptedActionException $iE) {
            // Nothing to do here.
        }
        return $response;
    }

    protected function response($controller, $implicitContent ,ResponseInterface $response) {
        /**
         * @var $executor ActionExecutor
         */
        $executor = $this->get('taoService')->get(ActionExecutor::SERVICE_ID);
        $executor->execute($controller , $response);
        echo $implicitContent;
    }

}