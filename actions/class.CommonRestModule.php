<?php

declare(strict_types=1);

use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;

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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */

/**
 * Class tao_actions_CommonRestModule
 * @OA\Info(title="TAO Rest API", version="1.0")
 */
abstract class tao_actions_CommonRestModule extends tao_actions_RestController
{
    /**
     * Entry point of API
     * If uri parameters is provided, it must be a valid uri
     * Depending on HTTP method, request is routed to crud function
     */
    public function index(): void
    {
        try {
            $uri = null;
            if ($this->hasRequestParameter('uri')) {
                $uri = $this->getRequestParameter('uri');
                if (! common_Utils::isUri($uri)) {
                    throw new common_exception_InvalidArgumentType();
                }
            }

            switch ($this->getRequestMethod()) {
                case 'GET':
                    $response = $this->get($uri);
                    break;
                case 'PUT':
                    $response = $this->put($uri);
                    break;
                case 'POST':
                    $response = $this->post();
                    break;
                case 'DELETE':
                    $response = $this->delete($uri);
                    break;
                default:
                    throw new common_exception_BadRequest($this->getRequestURI());
            }

            $this->returnSuccess($response);
        } catch (\Throwable $e) {
            if ($e instanceof \common_exception_ValidationFailed &&
                $alias = $this->reverseSearchAlias($e->getField())
            ) {
                $e = new \common_exception_ValidationFailed($alias, null, $e->getCode());
            }

            $this->returnFailure($e);
        }
    }

    /**
     * Return crud service
     *
     * @return tao_models_classes_CrudService
     * @throws common_Exception
     */
    protected function getCrudService()
    {
        if (! $this->service) {
            throw new common_Exception('Crud service is not set.');
        }
        return $this->service;
    }

    /**
     * Method to wrap fetching to service:
     * - get() if uri is not null
     * - getAll() if uri is null
     *
     * @param null $uri
     * @return mixed
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_PreConditionFailure
     */
    protected function get($uri = null)
    {
        if ($uri !== null) {
            if (! ($this->getCrudService()->isInScope($uri))) {
                throw new common_exception_PreConditionFailure('The URI must be a valid resource under the root Class');
            }
            return $this->getCrudService()->get($uri);
        }
        return $this->getCrudService()->getAll();
    }

    /**
     * Method to wrap deleting to service if uri is not null
     *
     * @param null $uri
     * @return mixed
     * @throws common_exception_BadRequest
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_PreConditionFailure
     */
    protected function delete($uri = null)
    {
        if ($uri === null) {
            //$data = $this->service->deleteAll();
            throw new common_exception_BadRequest('Delete method requires an uri parameter');
        }
        if (! ($this->getCrudService()->isInScope($uri))) {
            throw new common_exception_PreConditionFailure('The URI must be a valid resource under the root Class');
        }
        return $this->getCrudService()->delete($uri);
    }

    /**
     * Method to wrap creating to service
     *
     * @OA\Schema(
     *     schema="tao.CommonRestModule.CreatedResource",
     *     description="Created resource data",
     *     @OA\Property(
     *         property="uriResource",
     *         type="string",
     *         example="http://sample/first.rdf#i1536680377163171"
     *     ),
     *     @OA\Property(
     *         property="label",
     *         type="string"
     *     ),
     *     @OA\Property(
     *         property="comment",
     *         type="string"
     *     )
     * )
     * @OA\Schema(
     *     schema="tao.CommonRestModule.CreatedResourceResponse",
     *     description="Created resource data",
     *     allOf={
     *         @OA\Schema(ref="#/components/schemas/tao.RestTrait.BaseResponse")
     *     },
     *     @OA\Property(
     *         property="data",
     *         ref="#/components/schemas/tao.CommonRestModule.CreatedResource"
     *     )
     * )
     *
     * @return mixed
     * @throws common_Exception
     * @throws common_exception_RestApi
     */
    protected function post()
    {
        $parameters = $this->getParameters();
        try {
            return $this->getCrudService()->createFromArray($parameters);
        }
        /** @noinspection PhpRedundantCatchClauseInspection */
        catch (common_exception_PreConditionFailure $e) {
            throw new common_exception_RestApi($e->getMessage());
        }
    }

    /**
     * Method to wrap to updating to service if uri is not null
     *
     * @param $uri
     * @return mixed
     * @throws common_Exception
     * @throws common_exception_BadRequest
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_NoContent
     * @throws common_exception_PreConditionFailure
     * @throws common_exception_RestApi
     */
    protected function put($uri)
    {
        if ($uri === null) {
            throw new common_exception_BadRequest('Update method requires an uri parameter');
        }
        if (! ($this->getCrudService()->isInScope($uri))) {
            throw new common_exception_PreConditionFailure('The URI must be a valid resource under the root Class');
        }
        $parameters = $this->getParameters();
        try {
            return $this->getCrudService()->update($uri, $parameters);
        } catch (common_exception_PreConditionFailure $e) {
            throw new common_exception_RestApi($e->getMessage());
        }
    }

    /**
     * Returns all parameters that are URIs or Aliased with values
     *
     * @return array
     * @throws \common_exception_RestApi If a mandatory parameter is not found
     */
    protected function getParameters()
    {
        $effectiveParameters = [];
        $missedAliases = [];

        foreach ($this->getParametersAliases() as $checkParameterShort => $checkParameterUri) {
            if ($this->hasRequestParameter($checkParameterUri)) {
                $effectiveParameters[$checkParameterUri] = $this->getRequestParameter($checkParameterUri);
            } elseif ($this->hasRequestParameter($checkParameterShort)) {
                $effectiveParameters[$checkParameterUri] = $this->getRequestParameter($checkParameterShort);
            } elseif ($this->isRequiredParameter($checkParameterShort)) {
                $missedAliases[] = $checkParameterShort;
            }
        }

        if (count($missedAliases) > 0) {
            throw new \common_exception_RestApi(
                'Missed required parameters: ' . implode(', ', $missedAliases)
            );
        }

        return $effectiveParameters;
    }

    /**
     * Return required parameters by method
     * Shold return an array with key as HTTP method and value as array of parameters
     *
     * @return array
     */
    protected function getParametersRequirements()
    {
        return [
            'put' => ['uri'],
            'delete' => ['uri'],
        ];
    }

    /**
     * Default parameters aliases,
     * Map from get parameter name to class uri
     *
     * @return array
     */
    protected function getParametersAliases()
    {
        return [
            'label' => OntologyRdfs::RDFS_LABEL,
            'comment' => OntologyRdfs::RDFS_COMMENT,
            'type' => OntologyRdf::RDF_TYPE,
        ];
    }

    /**
     * @param string $paramName
     * @return string|false
     */
    protected function reverseSearchAlias($paramName)
    {
        return array_search($paramName, $this->getParametersAliases(), true);
    }

    /**
     * Defines if the parameter is mandatory according:
     * - getParametersRequirements array
     * - HTTP method
     *
     * @param $parameter, The alias name or uri of a parameter
     * @return bool
     */
    protected function isRequiredParameter($parameter)
    {
        $method = $this->getRequestMethod();
        $requirements = $this->getParametersRequirements();

        $requirements = array_change_key_case($requirements, CASE_LOWER);
        $method = strtolower($method);

        if (! isset($requirements[$method])) {
            return false;
        }

        if (in_array($parameter, $requirements[$method], true)) {
            return true;
        }

        $isRequired = false;

        //The requirements may have been declared using URIs, look up for the URI
        $aliases = $this->getParametersAliases();
        if (isset($aliases[$parameter])) {
            $isRequired = in_array($aliases[$parameter], $requirements[$method], true);
        }
        return $isRequired;
    }
}
