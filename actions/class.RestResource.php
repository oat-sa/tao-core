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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\accessControl\AclProxy;
use oat\oatbox\user\User;

/**
 * Class tao_actions_RestResourceController
 *
 * The rest controller to manage resource APIs
 */
class tao_actions_RestResource extends tao_actions_CommonModule
{
    use OntologyAwareTrait;

    const CLASS_PARAMETER = 'classUri';
    const RESOURCE_PARAMETER = 'uri';

    /**
     * Create a resource for class found into http request parameters
     *
     * If http method is GET, return the form data
     * If http method is POST, process form
     *
     * The POST request has to follow this structure:
     * array (
     *   'propertyUri' => 'value',
     *   'propertyUri1' => 'value1',
     *   'propertyUri2' => 'value2',
     *   'propertyUri3' => array(
     *      'value', 'value2',
     *    )
     * )
     *
     */
    public function create()
    {
        error_log('Why does a post to this action not print this log??? It says it\'s missing a classUri, but where is it checking that?');

        try {
            $class = $this->getClassParameter();
            /** @var User $user */
            $user = common_session_SessionManager::getSession()->getUser();
            if (!AclProxy::hasAccess($user, get_called_class(), __FUNCTION__, [self::CLASS_PARAMETER => $class->getUri()])) {
                throw new common_exception_Unauthorized(sprintf('Access refused to resource %s for user %s', $class->getUri(), $user->getIdentifier()));
            }
        } catch (common_Exception $e) {
            $this->returnFailure($e);
            return;
        }

        if ($this->isRequestGet()) {
            $this->returnSuccess($this->getForm($class)->getData());
        }

        if ($this->isRequestPost()) {
            try {
                $this->processForm($class);
            } catch (common_Exception $e) {
                $this->returnFailure($e);
            }
        }

        $this->returnFailure(new common_exception_MethodNotAllowed(__METHOD__ . ' only accepts GET or POST method'));
    }

    /**
     * Edit a resource found into http request parameters
     *
     * If http method is GET, return the form data
     * If http method is PUT, process form
     *
     * The PUT request has to follow this structure:
     * array (
     *   'propertyUri' => 'value',
     *   'propertyUri1' => 'value1',
     *   'propertyUri2' => 'value2',
     *   'propertyUri3' => array(
     *      'value', 'value2',
     *    )
     * )
     *
     */
    public function edit()
    {
        try {
            $resource = $this->getResourceParameter();
            /** @var User $user */
            $user = common_session_SessionManager::getSession()->getUser();
            if (!AclProxy::hasAccess($user, get_called_class(), __FUNCTION__, [self::RESOURCE_PARAMETER => $resource->getUri()])) {
                throw new common_exception_Unauthorized(sprintf('Access refused to resource %s for user %s', $resource->getUri(), $user->getIdentifier()));
            }
        } catch (common_Exception $e) {
            $this->returnFailure($e);
            return;
        }

        if ($this->isRequestGet()) {
            $this->returnSuccess($this->getForm($resource)->getData());
        }

        if ($this->isRequestPost()) {
            try {
                $this->processForm($resource);
            } catch (common_Exception $e) {
                $this->returnFailure($e);
            }
        }

        $this->returnFailure(new common_exception_MethodNotAllowed(__METHOD__ . ' only accepts GET or POST method'));
    }

    /**
     * Get the request parameters
     * If http method is POST read stream from php://input
     * Otherwise call parent method
     *
     * @return array
     */
    public function getRequestParameters()
    {
        if ($this->isRequestPost()) {
            $input = file_get_contents("php://input");
            $parameters = json_decode($input, true);
        } else {
            $parameters = parent::getRequestParameters();
        }

        return $parameters;
    }

    /**
     * Process the form submission
     * Bind the http data to form, validate, and save
     *
     * @param $instance
     * @throws common_Exception In case of runtime error
     */
    protected function processForm($instance)
    {
        $parameters = $this->getRequestParameters();
        $form = $this->getForm($instance)->bind($parameters);
        $report = $form->validate();
        if ($report->containsError()) {
            $this->returnValidationFailure($report);
        } else {
            $resource = $form->save();
            $this->returnSuccess(['uri' => $resource->getUri()]);
        }
    }

    /**
     * Get the form object to manage
     * The $instance should be a class for creation or resource in case of edit
     *
     * @param $instance
     * @return tao_actions_form_RestForm
     */
    protected function getForm($instance)
    {
        return new \tao_actions_form_RestForm($instance);
    }

    /**
     * Extract the resource from http request
     * The parameter 'uri' must exists and be a valid uri
     *
     * @return core_kernel_classes_Resource
     * @throws common_exception_MissingParameter
     */
    protected function getResourceParameter()
    {
        $parameters = $this->getRequestParameters();
        if (! isset($parameters[self::RESOURCE_PARAMETER])) {
            throw new \common_exception_MissingParameter(self::RESOURCE_PARAMETER, __CLASS__);
        }

        $uri = $parameters[self::RESOURCE_PARAMETER];
        if (empty($uri) || !common_Utils::isUri($uri)) {
            throw new \common_exception_MissingParameter(self::RESOURCE_PARAMETER, __CLASS__);
        }

        return $this->getResource($uri);
    }

    /**
     * Extract the class from http request
     * The parameter 'classUri' must exists and be a valid uri
     *
     * @return core_kernel_classes_Class
     * @throws common_exception_MissingParameter
     */
    protected function getClassParameter()
    {
        error_log('This doesn\'t seem to be the place where it is checking for classUri???');
        $parameters = $this->getRequestParameters();
        if (! isset($parameters[self::CLASS_PARAMETER])) {
            throw new \common_exception_MissingParameter(self::CLASS_PARAMETER, __CLASS__);
        }

        $uri = $parameters[self::CLASS_PARAMETER];
        if (empty($uri) || !common_Utils::isUri($uri)) {
            throw new \common_exception_MissingParameter(self::CLASS_PARAMETER, __CLASS__);
        }

        return $this->getClass($uri);
    }

    /**
     * Transform a report to http response with 422 code and report error messages
     *
     * @param common_report_Report $report
     * @param bool $withMessage
     */
    protected function returnValidationFailure(common_report_Report $report, $withMessage=true)
    {
        $data = ['data' => []];
        /** @var common_report_Report $error */
        foreach ($report->getErrors() as $error) {
            $data['data'][$error->getData()] = $error->getMessage();
        }

        if ($withMessage) {
            $data['success'] = false;
            $data['errorCode'] = 400;
            $data['errorMsg'] = 'Some fields are invalid';
            $data['version'] = TAO_VERSION;
        }

        $this->returnJson($data, 400);
        exit(0);
    }

    /**
     * Return an error reponse following the given exception
     * An exception handler manages http code, avoid to use returnJson to add unneeded header
     *
     * @param Exception $exception
     * @param bool $withMessage
     */
    protected function returnFailure(Exception $exception, $withMessage=true)
    {
        $data = array();
        if ($withMessage) {
            $data['success'] = false;
            $data['errorCode'] = 500;
            $data['version'] = TAO_VERSION;
            if ($exception instanceof common_exception_UserReadableException) {
                $data['errorMsg'] = $exception->getUserMessage();
            } else {
                common_Logger::w(__CLASS__ . ' : ' . $exception->getMessage());
                $data['errorMsg'] = __('Unexpected error. Please contact administrator');
            }
        }

        $this->returnJson($data, 500);
        exit(0);
    }

    /**
     * Return a successful http response
     *
     * @param array $rawData
     * @param bool $withMessage
     */
    protected function returnSuccess($rawData = array(), $withMessage=true)
    {
        $data = array();
        if ($withMessage) {
            $data['success'] = true;
            $data['data'] = $rawData;
            $data['version'] = TAO_VERSION;
        } else {
            $data = $rawData;
        }

        $this->returnJson($data);
        exit(0);
    }

}
