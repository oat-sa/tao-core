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


use oat\tao\model\mvc\Application\Handler\ImplicitHeaders;
use oat\tao\model\mvc\psr7\ActionExecutor;
use Psr\Http\Message\ResponseInterface;

class ControllerRendering extends AbstractTaoMiddleware
{

    /**
     * @param $controller
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function response($controller ,ResponseInterface $response) {
        /**
         * @var $executor ActionExecutor
         */
        $executor = $this->getServiceLocator()->get(ActionExecutor::SERVICE_ID);

        $response = $executor->execute($controller  , $response);

        return $response;
    }

    public function __invoke($request, $response, $args)
    {
        $controller = $args['controller'];
        $response = $this->response($controller , $response);

        $implicitHeadersHandler = new ImplicitHeaders();
        $response = $implicitHeadersHandler->catchHeaders()->setUpResponse($response);

        return $response;
    }

}