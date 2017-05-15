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

use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\accessControl\data\DataAccessControl;
use oat\tao\model\accessControl\data\PermissionException;
use oat\tao\model\accessControl\func\AclProxy as FuncProxy;
use oat\tao\model\mvc\Application\Resolution;
use oat\tao\model\mvc\psr7\Resolver;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware to resolve route to controller
 * Class TaoResolver
 * @package oat\tao\model\mvc\middleware
 */
class TaoAuthenticate extends AbstractTaoMiddleware
{


    protected function verifyAuthorization(ServerRequestInterface $request , Resolution $resolution) {


    }


    public function __invoke( $request,  $response,  $args)
    {
        /**
         * @var $resolution Resolution
         */
        $resolution = $args['resolution'];

        $post = $request->getParsedBody();
        if(is_null($post)) {
            $post = [];
        }
        $params   = array_merge($request->getQueryParams() , $post);

        $user = \common_session_SessionManager::getSession()->getUser();
        if (!AclProxy::hasAccess($user, $resolution->getControllerClass(), $resolution->getMethodName(), $params)) {
            $func  = new FuncProxy();
            $data  = new DataAccessControl();
            //now go into details to see which kind of permissions are not correct
            if($func->hasAccess($user, $resolution->getControllerClass(), $resolution->getMethodName(), $params) &&
                !$data->hasAccess($user, $resolution->getControllerClass(), $resolution->getMethodName(), $params)){

                throw new PermissionException($user->getIdentifier(), $resolution->getMethodName() , $resolution->getControllerClass(), $params);
            }

            throw new \tao_models_classes_AccessDeniedException($user->getIdentifier(), $resolution->getMethodName() , $resolution->getControllerClass(), $resolution->getExtensionId());
        }

        return $response;
    }

}