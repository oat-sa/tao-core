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

namespace oat\tao\controller\api;

use common_Exception;
use common_exception_MethodNotAllowed;
use common_exception_MissingParameter;
use common_exception_RestApi;
use common_exception_ValidationFailed;
use common_Utils;
use core_kernel_classes_Resource;
use oat\generis\model\user\UserRdf;
use oat\oatbox\service\ServiceManager;
use tao_actions_CommonRestModule;
use tao_actions_RestController;
use tao_models_classes_UserService;

/**
 * @OA\Post(
 *     path="tao/UserAPI",
 *     summary="Create new user",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/tao.User.New")
 *         )
 *     ),
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
class Users extends tao_actions_CommonRestModule
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
     *     required={"login", "password", "userLanguage", "roles"}
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
     *         property="userLanguage",
     *         type="string",
     *         description="Interface language uri"
     *     ),
     *     @OA\Property(
     *         property="defaultLanguage",
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
     *     ),
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
    protected function getParametersRequirements()
    {
        return [
            'post' => ['login', 'password', 'userLanguage', 'roles']
        ];
    }

    /**
     * @return array
     */
    protected function getMandatoryUriParameters()
    {
        return ['userLanguage', 'defaultLanguage'];
    }

    /**
     * @return array
     */
    protected function getGuardedProperties()
    {
        return ['login', 'password', 'roles', 'type'];
    }

    /**
     * @return array
     */
    protected function getParametersAliases(){
        return array_merge(parent::getParametersAliases(), [
            'login' => UserRdf::PROPERTY_LOGIN,
            'password' => UserRdf::PROPERTY_PASSWORD,
            'userLanguage' => UserRdf::PROPERTY_UILG,
            'defaultLanguage' => UserRdf::PROPERTY_DEFLG,
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
    public function get($uri = null) {
        throw new \common_exception_NotImplemented('Not implemented');
    }

    /**
     * @param string $uri
     * @return mixed
     * @throws \common_exception_NotImplemented
     */
    public function put($uri) {
        throw new \common_exception_NotImplemented('Not implemented');
    }

    /**
     * @param string $uri
     * @return mixed
     * @throws \common_exception_NotImplemented
     */
    public function delete($uri = null) {
        throw new \common_exception_NotImplemented('Not implemented');
    }

    /**
     * @return mixed
     * @throws common_Exception
     * @throws common_exception_RestApi
     */
    public function post()
    {
        /** @var tao_models_classes_UserService $userService */
        $userService = ServiceManager::getServiceManager()->get(tao_models_classes_UserService::SERVICE_ID);

        if (!$userService->getOption(tao_models_classes_UserService::OPTION_ALLOW_API)) {
            throw new common_exception_MethodNotAllowed();
        }

        $parameters = $this->getParameters();

        try {

            $roles = $this->processRoles($parameters);
            $login = $parameters[UserRdf::PROPERTY_LOGIN];
            $password = $parameters[UserRdf::PROPERTY_PASSWORD];

            $guarded = array_intersect_key($this->getParametersAliases(), array_flip($this->getGuardedProperties()));
            $parameters = array_filter($parameters, function ($key) use ($guarded) {
                return !in_array($key, $guarded, true);
            }, ARRAY_FILTER_USE_KEY);

            $this->validateUris($parameters);

            /** @var core_kernel_classes_Resource $user */
            $user = $userService->addUser($login, $password, $this->getResource(array_shift($roles)));

            foreach ($roles as $role) {
                $userService->attachRole($user, $this->getResource($role));
            }

            $user->setPropertiesValues($parameters);

            return $user;
        } catch (common_Exception $e) {
            throw new common_exception_RestApi($e->getMessage());
        }
    }

    /**
     * @param array $parameters
     * @return array
     * @throws common_exception_MissingParameter
     */
    protected function processRoles(array $parameters)
    {
        $roles = [];

        if (!empty($parameters[UserRdf::PROPERTY_ROLES])) {
            $roles = array_filter(explode(',', $parameters[UserRdf::PROPERTY_ROLES]), function ($role) {
                return common_Utils::isUri($role);
            });
        }

        if (!count($roles)) {
            throw new \common_exception_MissingParameter('roles');
        }

        return $roles;
    }

    /**
     * @param array $parameters
     * @throws common_exception_ValidationFailed
     */
    protected function validateUris(array $parameters)
    {
        $uriProperties = array_intersect_key($this->getParametersAliases(), array_flip($this->getMandatoryUriParameters()));

        foreach ($parameters as $key => $value) {
            if (in_array($key, $uriProperties, true) && !common_Utils::isUri($value)) {
                throw new common_exception_ValidationFailed(null, __("Validation for field '%s' has failed. Valid URI expected", array_search($key, $uriProperties, true)));
            }
        }
    }
}
