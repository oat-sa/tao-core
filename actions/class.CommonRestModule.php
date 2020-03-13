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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;

/**
 * Class tao_actions_CommonRestModule
 *
 * @OA\Info(title="TAO Rest API", version="1.0")
 */
abstract class tao_actions_CommonRestModule extends tao_actions_RestController
{
    /** @var tao_models_classes_CrudService */
    protected $service;

    /**
     * Entry point of API
     * If uri parameters is provided, it must be a valid uri
     * Depending on HTTP method, request is routed to crud function
     *
     * @throws common_exception_NotImplemented
     */
    public function index()
    {
        try {
            $uri = $this->getUriFromRequest();
            $request = $this->getPsrRequest();

            switch ($request->getMethod()) {
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
                    throw new common_exception_BadRequest($request->getUri()->getPath());
            }

            $this->returnSuccess($response);
        } catch (Exception $e) {
            if (
                $e instanceof \common_exception_ValidationFailed &&
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
     * @throws common_Exception
     *
     * @return tao_models_classes_CrudService
     */
    protected function getCrudService()
    {
        if (!$this->service) {
            throw new common_Exception('Crud service is not set.');
        }

        return $this->service;
    }

    /**
     * Method to wrap fetching to service:
     * - get() if uri is not null
     * - getAll() if uri is null
     *
     * @param string|null $uri
     *
     * @throws common_Exception
     * @throws common_Exception_NoContent
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_PreConditionFailure
     *
     * @return object|stdClass
     */
    protected function get($uri = null)
    {
        if ($uri !== null) {
            if ($this->getCrudService()->isInScope($uri) === false) {
                throw new common_exception_PreConditionFailure(
                    'The URI must be a valid resource under the root Class'
                );
            }

            return $this->getCrudService()->get($uri);
        } else {
            return $this->getCrudService()->getAll();
        }
    }

    /**
     * Method to wrap deleting to service if uri is not null
     *
     * @param string|null $uri
     *
     * @throws common_Exception
     * @throws common_exception_BadRequest
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_NoContent
     * @throws common_exception_PreConditionFailure
     */
    protected function delete($uri = null)
    {
        if ($uri === null) {
            throw new common_exception_BadRequest('Delete method requires an uri parameter');
        } elseif ($this->getCrudService()->isInScope($uri) === false) {
            throw new common_exception_PreConditionFailure(
                'The URI must be a valid resource under the root Class'
            );
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
     * @throws common_Exception
     * @throws common_exception_RestApi
     *
     * @return mixed
     */
    protected function post()
    {
        try {
            return $this->getCrudService()->createFromArray($this->getParameters());
        } catch (common_exception_PreConditionFailure $e) {
            throw new common_exception_RestApi($e->getMessage());
        }
    }

    /**
     * Method to wrap to updating to service if uri is not null
     *
     * @param string|null $uri
     *
     * @throws common_Exception
     * @throws common_exception_BadRequest
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_NoContent
     * @throws common_exception_PreConditionFailure
     * @throws common_exception_RestApi
     *
     * @return mixed
     */
    protected function put($uri)
    {
        if ($uri === null) {
            throw new common_exception_BadRequest('Update method requires an uri parameter');
        } elseif ($this->getCrudService()->isInScope($uri) === false) {
            throw new common_exception_PreConditionFailure(
                'The URI must be a valid resource under the root Class'
            );
        }

        try {
            return $this->getCrudService()->update($uri, $this->getParameters());
        } catch (common_exception_PreConditionFailure $e) {
            throw new common_exception_RestApi($e->getMessage());
        }
    }

    /**
     * Returns all parameters that are URIs or Aliased with values
     *
     * @throws \common_exception_RestApi If a mandatory parameter is not found
     *
     * @return array
     */
    protected function getParameters()
    {
        $effectiveParameters = [];
        $missedAliases = [];

        if (!is_array($parameters = $this->getPsrRequest()->getParsedBody())) {
            $parameters = [];
        }

        foreach ($this->getParametersAliases() as $checkParameterShort => $checkParameterUri) {
            if (array_key_exists($checkParameterUri, $parameters)) {
                $effectiveParameters[$checkParameterUri] = $parameters[$checkParameterUri];
            } elseif (array_key_exists($checkParameterShort, $parameters)) {
                $effectiveParameters[$checkParameterUri] = $parameters[$checkParameterShort];
            } elseif ($this->isRequiredParameter($checkParameterShort)) {
                $missedAliases[] = $checkParameterShort;
            }
        }

        if (!empty($missedAliases)) {
            throw new \common_exception_RestApi(
                'Missed required parameters: ' . implode(', ', $missedAliases)
            );
        }

        return $effectiveParameters;
    }

    /**
     * Return required parameters by method
     * Should return an array with key as HTTP method and value as array of parameters
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
     *
     * @return false|int|string
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
     * @param string $parameter The alias name or uri of a parameter
     *
     * @return bool
     */
    protected function isRequiredParameter($parameter)
    {
        $requirements = array_change_key_case($this->getParametersRequirements(), CASE_LOWER);
        $method = strtolower($this->getPsrRequest()->getMethod());

        if (!isset($requirements[$method])) {
            return false;
        } elseif (in_array($parameter, $requirements[$method], true)) {
            return true;
        }

        $isRequired = false;

        // The requirements may have been declared using URIs, look up for the URI
        $aliases = $this->getParametersAliases();

        if (isset($aliases[$parameter])) {
            $isRequired = in_array($aliases[$parameter], $requirements[$method], true);
        }

        return $isRequired;
    }

    /**
     * @throws common_exception_InvalidArgumentType
     *
     * @return string|null
     */
    protected function getUriFromRequest()
    {
        $uri = null;

        $request = $this->getPsrRequest();
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        if (is_array($parsedBody) && array_key_exists('uri', $parsedBody)) {
            $uri = $parsedBody['uri'];
        } elseif (array_key_exists('uri', $queryParams)) {
            $uri = $queryParams['uri'];
        }

        if ($uri !== null && common_Utils::isUri($uri) === false) {
            throw new common_exception_InvalidArgumentType();
        }

        return $uri;
    }
}
