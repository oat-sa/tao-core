<?php

use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\accessControl\data\DataAccessControl;

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
abstract class tao_actions_CommonRestModule extends tao_actions_RestController
{
    const CLASS_FILTER_SUB_CLASSES = 'subClasses';
    const CLASS_FILTER_INSTANCES = 'instances';

    /**
     * Entry point of API
     * If uri parameters is provided, it must be a valid uri
     * Depending on HTTP method, request is routed to crud function
     *
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @throws common_exception_NotImplemented
     * @deprecated Use resources() instead
     */
    public function index($advancedAclUsage = false)
    {
        try {
            $uri = $this->getUriFromRequestParameter();

            switch ($this->getRequestMethod()) {
                case 'GET':
                    $response = $this->restGetResource($uri, $advancedAclUsage);
                    break;
                case 'PUT':
                    $response = $this->restPutResource($uri, $advancedAclUsage);
                    break;
                case 'POST':
                    $response = $this->restPostResource($advancedAclUsage);
                    break;
                case 'DELETE':
                    $response = $this->restDeleteResource($uri, $advancedAclUsage);
                    break;
                default:
                    throw new common_exception_BadRequest($this->getRequestURI());
            }

            $this->returnSuccess($response);
        } catch (Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Endpoint to perform CRUD actions on \core_kernel_classes_Resource(s)
     */
    public function resources()
    {
        $this->index(true);
    }

    /**
     * Endpoint to perform CRUD actions on \core_kernel_classes_Class(es)
     */
    public function classes()
    {
        try {
            $uri = $this->getUriFromRequestParameter();

            switch ($this->getRequestMethod()) {
                case 'GET':
                    $response = $this->restGetClass($this->getRequestParameter('filter'), $uri);
                    break;
                case 'POST':
                    $response = $this->restPostClass($uri);
                    break;
                case 'DELETE':
                    $response = $this->restDeleteClass($uri);
                    break;
                default:
                    throw new common_exception_BadRequest($this->getRequestURI());
            }

            $this->returnSuccess($response);
        } catch (Exception $e) {
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
        if (!$this->service) {
            throw new common_Exception('Crud service is not set.');
        }

        return $this->service;
    }

    /**
     * Returns sub-classes or sub-resources (depending on the filter parameter value) of a given class.
     * If no class URI is specified, the current root class will be used.
     *
     * @param string $filter
     * @param string|null $uri
     *
     * @return array
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws common_exception_BadRequest
     */
    protected function restGetClass($filter, $uri = null)
    {
        if (!$uri) {
            $uri = $this->getCrudService()->getRootClass()->getUri();
        }

        $this->enforceAcl($uri, 'READ');

        $class = $this->getClass($uri);

        if (!$class->isClass() || !$class->exists()) {
            throw new common_exception_NotFound(sprintf('Class `%s` does not exist', $uri));
        }

        return $this->getFormattedResources($this->getSubResourcesByFilter($filter, $class));
    }

    /**
     * Creates a new sub-class for a given class.
     * If no class URI is specified, the current root class will be used.
     *
     * @param string|null $parentClassUri
     *
     * @return stdClass
     *
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    protected function restPostClass($parentClassUri = null)
    {
        if (!$parentClassUri) {
            $parentClassUri = $this->getCrudService()->getRootClass()->getUri();
        }

        $this->enforceAcl($parentClassUri, 'WRITE');

        $parentClass = $this->getClass($parentClassUri);

        if (!$parentClass->isClass() || !$parentClass->exists()) {
            throw new common_exception_NotFound(sprintf('Class `%s` does not exist', $parentClassUri));
        }

        $subClass = $parentClass->createSubClass($this->getRequestParameter('label'));

        return $this->getFormattedResource($subClass);
    }

    /**
     * Deletes the given class.
     *
     * @param string $uri
     *
     * @return array
     * @throws common_Exception
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    protected function restDeleteClass($uri)
    {
        $this->enforceAcl($uri, 'WRITE');

        $class = $this->getClass($uri);

        if (!$class->isClass() || !$class->exists()) {
            throw new common_exception_NotFound(sprintf('Class `%s` does not exist', $uri));
        }

        return $this->getCrudService()->delete($uri);
    }

    /**
     * Returns instances of a given resource.
     * If no resource URI is specified, the current root class will be used.
     *
     * @param string|null $uri
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @return mixed
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_PreConditionFailure
     */
    protected function restGetResource($uri = null, $advancedAclUsage = false)
    {
        return $this->get($uri, $advancedAclUsage);
    }

    /**
     * Updates the specified resource properties.
     *
     * @param string $uri
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @return mixed
     * @throws common_exception_BadRequest
     * @throws common_exception_MissingParameter
     * @throws common_exception_PreConditionFailure
     */
    protected function restPutResource($uri, $advancedAclUsage = false)
    {
        return $this->put($uri, $advancedAclUsage);
    }

    /**
     * Creates a new resource under the current root class.
     *
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @return mixed
     * @throws common_exception_MissingParameter
     */
    protected function restPostResource($advancedAclUsage = false)
    {
        return $this->post($advancedAclUsage);
    }

    /**
     * Deletes the given resource.
     *
     * @param string|null $uri
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @return mixed
     * @throws common_exception_BadRequest
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_PreConditionFailure
     */
    protected function restDeleteResource($uri = null, $advancedAclUsage = false)
    {
        return $this->delete($uri, $advancedAclUsage);
    }

    /**
     * Method to wrap fetching to service:
     * - get() if uri is not null
     * - getAll() if uri is null
     *
     * @param string|null $uri
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @return mixed
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_PreConditionFailure
     *
     * @deprecated Use getResource instead
     */
    protected function get($uri = null, $advancedAclUsage = false)
    {
        if (!is_null($uri)) {
            if (!$this->getCrudService()->isInScope($uri)) {
                throw new common_exception_PreConditionFailure('The URI must be a valid resource under the root Class');
            }

            if ($advancedAclUsage) {
                $this->enforceAcl($uri, 'READ');
            }

            return $this->getCrudService()->get($uri);
        }

        if ($advancedAclUsage) {
            $this->enforceAcl($this->getCrudService()->getRootClass()->getUri(), 'READ');
        }

        return $this->getCrudService()->getAll();
    }

    /**
     * Method to wrap deleting to service if uri is not null
     *
     * @param string|null $uri
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @return mixed
     * @throws common_exception_BadRequest
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_PreConditionFailure
     *
     * @deprecated Use deleteResource instead
     */
    protected function delete($uri = null, $advancedAclUsage = false)
    {
        if (is_null($uri)) {
            throw new common_exception_BadRequest('Delete method requires an uri parameter');
        }
        if (!$this->getCrudService()->isInScope($uri)) {
            throw new common_exception_PreConditionFailure('The URI must be a valid resource under the root Class');
        }

        if ($advancedAclUsage) {
            $this->enforceAcl($uri, 'WRITE');
        }

        return $this->getCrudService()->delete($uri);
    }

    /**
     * Method to wrap creating to service
     *
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @return mixed
     * @throws common_exception_MissingParameter
     *
     * @deprecated Use postResource instead
     */
    protected function post($advancedAclUsage = false)
    {
        $parameters = $this->getParameters();

        if ($advancedAclUsage) {
            $this->enforceAcl($this->getCrudService()->getRootClass()->getUri(), 'WRITE');
        }

        return $this->getCrudService()->createFromArray($parameters);
    }

    /**
     * Method to wrap to updating to service if uri is not null
     *
     * @param string $uri
     * @param bool $advancedAclUsage Whether the resource ACL should be checked or not.
     *
     * @return mixed
     * @throws common_exception_BadRequest
     * @throws common_exception_MissingParameter
     * @throws common_exception_PreConditionFailure
     *
     * @deprecated Use putResource instead
     */
    protected function put($uri, $advancedAclUsage = false)
    {
        if (is_null($uri)) {
            throw new common_exception_BadRequest('Update method requires an uri parameter');
        }
        if (!$this->getCrudService()->isInScope($uri)) {
            throw new common_exception_PreConditionFailure('The URI must be a valid resource under the root Class');
        }

        if ($advancedAclUsage) {
            $this->enforceAcl($uri, 'WRITE');
        }

        $parameters = $this->getParameters();
        return $this->getCrudService()->update($uri, $parameters);
    }

    /**
     * Returns all parameters that are URIs or Aliased with values
     *
     * @return array
     * @throws \common_exception_MissingParameter If a mandatory parameter is not found
     */
    protected function getParameters()
    {
        $aliasedParameters = $this->getParametersAliases();
        $effectiveParameters = [];
        foreach ($aliasedParameters as $checkParameterShort => $checkParameterUri) {
            if ($this->hasRequestParameter($checkParameterShort)) {
                $effectiveParameters[$checkParameterUri] = $this->getRequestParameter($checkParameterShort);
            }
            if ($this->hasRequestParameter($checkParameterUri)) {
                $effectiveParameters[$checkParameterUri] = $this->getRequestParameter($checkParameterUri);
            }
            if ($this->isRequiredParameter($checkParameterShort) and !isset($effectiveParameters[$checkParameterUri])) {
                throw new \common_exception_MissingParameter($checkParameterShort, $this->getRequestURI());
            }
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
     * Defines if the parameter is mandatory according:
     * - getParametersRequirements array
     * - HTTP method
     *
     * @param $parameter , The alias name or uri of a parameter
     *
     * @return bool
     */
    private function isRequiredParameter($parameter)
    {
        $method = $this->getRequestMethod();
        $requirements = $this->getParametersRequirements();

        $requirements = array_change_key_case($requirements, CASE_LOWER);
        $method = strtolower($method);

        if (!isset($requirements[$method])) {
            return false;
        }

        if (in_array($parameter, $requirements[$method])) {
            return true;
        }

        $isRequired = false;

        //The requirements may have been declared using URIs, look up for the URI
        $aliases = $this->getParametersAliases();
        if (isset($aliases[$parameter])) {
            $isRequired = in_array($aliases[$parameter], $requirements[$method]);
        }
        return $isRequired;
    }

    /**
     * @param string $resourceId
     * @param string $permission
     *
     * @throws common_exception_Error
     * @throws common_exception_Unauthorized
     */
    private function enforceAcl($resourceId, $permission)
    {
        $user = $this->getSession()->getUser();

        if (!(new DataAccessControl())->hasPrivileges($user, [$resourceId => $permission])) {
            throw new common_exception_Unauthorized();
        }
    }

    /**
     * @param core_kernel_classes_Resource[] $resources
     *
     * @return array
     */
    private function getFormattedResources(array $resources)
    {
        return array_map([$this, 'getFormattedResource'], $resources);
    }

    /**
     * @param core_kernel_classes_Resource $resource
     *
     * @return stdClass
     * @throws common_exception_Error
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_NoContent
     */
    private function getFormattedResource(core_kernel_classes_Resource $resource)
    {
        $formatter = new core_kernel_classes_ResourceFormatter();

        return $formatter->getResourceDescription($resource, false);
    }

    private function getUriFromRequestParameter()
    {
        $uri = null;

        if ($this->hasRequestParameter('uri')) {
            $uri = $this->getRequestParameter('uri');

            if (!common_Utils::isUri($uri)) {
                throw new common_exception_InvalidArgumentType();
            }
        }

        return $uri;
    }

    /**
     * @param string $filter
     * @param core_kernel_classes_Class $class
     *
     * @return core_kernel_classes_Class[]|core_kernel_classes_Resource[]
     * @throws common_exception_BadRequest
     */
    private function getSubResourcesByFilter($filter, core_kernel_classes_Class $class)
    {
        if ($filter == static::CLASS_FILTER_SUB_CLASSES) {
            $subResources = $class->getSubClasses();
        } elseif ($filter == static::CLASS_FILTER_INSTANCES) {
            $subResources = $class->getInstances();
        } else {
            throw new common_exception_BadRequest(
                sprintf(
                    'Filter `%s` is invalid, valid filters are : `%s` and `%s`',
                    $filter,
                    static::CLASS_FILTER_INSTANCES,
                    static::CLASS_FILTER_SUB_CLASSES
                )
            );
        }

        return $subResources;
    }
}
