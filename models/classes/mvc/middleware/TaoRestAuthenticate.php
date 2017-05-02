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

use oat\tao\model\mvc\psr7\Resolver;

/**
 * Middleware to resolve route to controller
 * Class TaoResolver
 * @package oat\tao\model\mvc\middleware
 */
class TaoRestAuthenticate extends AbstractTaoMiddleware
{

    public function __invoke( $request,  $response,  $args)
    {
        /**
         * @var $resolver Resolver
         */
        $resolver = $this->container->get('resolver');
        $resolver->setRequest($request);

        //if the controller is a rest controller we try to authenticate the user
        $controllerClass = $resolver->getControllerClass();

        if (is_subclass_of($controllerClass, \tao_actions_RestController::class)) {
            $authAdapter = new \tao_models_classes_HttpBasicAuthAdapter(\common_http_Request::currentRequest());
            try {
                $user = $authAdapter->authenticate();
                $session = new \common_session_RestSession($user);
                \common_session_SessionManager::startSession($session);
            } catch (\common_user_auth_AuthFailedException $e) {
                /**
                 * @todo change for prs7 response
                 */
                $data['success'] = false;
                $data['errorCode'] = '401';
                $data['errorMsg'] = 'You are not authorized to access this functionality.';
                $data['version'] = TAO_VERSION;

                $response = $response->withAddedHeader('WWW-Authenticate' , 'Basic realm="' . GENERIS_INSTANCE_NAME . '"')
                                    ->withStatus(401)
                                    ->withAddedHeader('Content-Type' , 'application/json');

                $response->getBody()->write(json_encode($data));

            }
        }
        return $response;
    }

}