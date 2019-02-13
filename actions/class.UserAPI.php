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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\user\UserRdf;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\user\TaoRoles;

/**
 * @OA\Info(title="TAO User API", version="1.0")
 * @OA\Post(
 *     path="tao/UserAPI"
 *     summary="Create new user"
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/tao.User.New")
 *         )
 *     )
 *     @OA\Response(
 *         response="200",
 *         description="User created",
 *         @OA\JsonContent(ref="#/components/schemas/tao.CommonRestModule.CreatedResourceResponse")
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Invalid request data",
 *         @OA\JsonContent(ref="#/components/schemas/tao.RestTrait.FailureResponse")
 *     )
 * )
 */
class tao_actions_UserAPI extends tao_actions_CommonRestModule
{
    /**
     * @OA\Schema(
     *     schema="tao.User.New",
     *     type="object",
     *     allOf={
     *          @OA\Schema(ref="#/components/schemas/tao.GenerisClass.Search"),
     *          @OA\Schema(ref="#/components/schemas/tao.User.Update")
     *     },
     *     @OA\Property(
     *         property="login",
     *         type="string",
     *         description="Login"
     *     ),
     *     required={"login", "password"}
     * )
     * @OA\Schema(
     *     schema="tao.User.Update",
     *     type="object",
     *     @OA\Property(
     *         property="login",
     *         type="string",
     *         description="Login"
     *     ),
     *     @OA\Property(
     *         property="password",
     *         type="string",
     *         description="Password"
     *     ),
     *     @OA\Property(
     *         property="uiLg",
     *         type="string",
     *         description="Interface language uri"
     *     ),
     *     @OA\Property(
     *         property="defLg",
     *         type="string",
     *         description="Default language uri"
     *     ),
     *     @OA\Property(
     *         property="firstName",
     *         type="string",
     *         description="First name"
     *     ),
     *     @OA\Property(
     *         property="lastName",
     *         type="string",
     *         description="Last name"
     *     ),
     *     @OA\Property(
     *         property="mail",
     *         type="string",
     *         description="Email"
     *     )
     *     @OA\Property(
     *         property="roles",
     *         type="string",
     *         description="Comma-separated list of roles (URIs)"
     *     )
     * )
     */

    /**
     * Optional Requirements for parameters to be sent on every service
     */
    protected function getParametersRequirements() {
        return [
            'post' => ["login", "password"]
        ];
    }

    /**
     * @return array
     */
    protected function getParametersAliases(){
        return array_merge(parent::getParametersAliases(), [
            'login' => UserRdf::PROPERTY_LOGIN,
            'password' => UserRdf::PROPERTY_PASSWORD,
            'uiLg' => UserRdf::PROPERTY_UILG,
            'defLg' => UserRdf::PROPERTY_DEFLG,
            'firstName'=> UserRdf::PROPERTY_FIRSTNAME,
            'lastName' => UserRdf::PROPERTY_LASTNAME,
            'mail' => UserRdf::PROPERTY_MAIL,
            'roles' => UserRdf::PROPERTY_ROLES
        ]);
    }

    /**
     * @param null $uri
     * @return mixed
     * @throws \common_exception_NotImplemented
     */
    protected function get($uri = null) {
        throw new \common_exception_NotImplemented('Not implemented');
    }

    /**
     * @param string $uri
     * @return mixed
     * @throws \common_exception_NotImplemented
     */
    protected function put($uri) {
        throw new \common_exception_NotImplemented('Not implemented');
    }

    /**
     * @param string $uri
     * @return mixed
     * @throws \common_exception_NotImplemented
     */
    protected function delete($uri = null) {
        throw new \common_exception_NotImplemented('Not implemented');
    }

    /**
     * @return mixed
     * @throws common_Exception
     * @throws common_exception_RestApi
     */
    protected function post()
    {
        /** @var tao_models_classes_UserService $userService */
        $userService = ServiceManager::getServiceManager()->get(tao_models_classes_UserService::SERVICE_ID);

        if (!$userService->getOption(tao_models_classes_UserService::OPTION_ALLOW_API)) {
            throw new common_exception_MethodNotAllowed();
        }

        $parameters = $this->getParameters();

        if (!isset($parameters[UserRdf::PROPERTY_LOGIN])) {
            throw new \common_exception_MissingParameter("login");
        }

        if (!isset($parameters[UserRdf::PROPERTY_PASSWORD])) {
            throw new \common_exception_MissingParameter("password");
        }

        try {
            /** @var core_kernel_classes_Resource $user */
            $user = $userService->addUser(
                $parameters[UserRdf::PROPERTY_LOGIN],
                $parameters[UserRdf::PROPERTY_PASSWORD],
                $this->getResource(TaoRoles::BASE_USER)
            );

            if (!empty($parameters[UserRdf::PROPERTY_ROLES])) {
                $roles = explode(',', $parameters[UserRdf::PROPERTY_ROLES]);
                foreach ($roles as $role) {
                    if (!common_Utils::isUri($role)) {
                        continue;
                    }
                    $userService->attachRole($user, $this->getResource($role));
                }
                unset($parameters[UserRdf::PROPERTY_ROLES]);
            }

            if (isset($parameters[UserRdf::PROPERTY_DEFLG]) && !common_Utils::isUri($parameters[UserRdf::PROPERTY_DEFLG])) {
                unset($parameters[UserRdf::PROPERTY_DEFLG]);
            }

            if (isset($parameters[UserRdf::PROPERTY_UILG]) && !common_Utils::isUri($parameters[UserRdf::PROPERTY_UILG])) {
                unset($parameters[UserRdf::PROPERTY_UILG]);
            }

            $user->setPropertiesValues($parameters);

            return $user;
        } catch (core_kernel_users_Exception $e) {
            throw new common_exception_RestApi($e->getMessage());
        }
    }
}
