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

/**
 * Class tao_actions_RestResourceController
 *
 * The rest controller to manage resource APIs
 */
class tao_actions_RestResourceController extends tao_actions_RestController
{
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
     * @requiresRight classUri WRITE
     */
    public function create()
    {
        if ($this->isRequestGet()) {
            try {
                $this->returnSuccess($this->getForm($this->getClassParameter())->getData());
            } catch (common_Exception $e) {
                $this->returnFailure($e);
            }
        }

        if ($this->isRequestPost()) {
            try {
                $this->processForm($this->getClassParameter());
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
     * @requiresRight uri WRITE
     */
    public function edit()
    {
        if ($this->isRequestGet()) {
            try {
                $this->returnSuccess($this->getForm($this->getResourceParameter())->getData());
            } catch (common_Exception $e) {
                $this->returnFailure($e);
            }
        }

        if ($this->isRequestPut()) {
            try {
                $this->processForm($this->getResourceParameter());
            } catch (common_Exception $e) {
                $this->returnFailure($e);
            }
        }

        $this->returnFailure(new common_exception_MethodNotAllowed(__METHOD__ . ' only accepts GET or PUT method'));
    }

    /**
     * Process the form submission
     * Bind the http data to form, validate, and save
     *
     * @param $instance
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
        if (! $this->hasRequestParameter(self::RESOURCE_PARAMETER)) {
            throw new \common_exception_MissingParameter(self::RESOURCE_PARAMETER, __CLASS__);
        }

        $uri = $this->getRequestParameter(self::RESOURCE_PARAMETER);
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
        if (! $this->hasRequestParameter(self::CLASS_PARAMETER)) {
            throw new \common_exception_MissingParameter(self::CLASS_PARAMETER, __CLASS__);
        }

        $uri = $this->getRequestParameter(self::CLASS_PARAMETER);
        if (empty($uri) || !common_Utils::isUri($uri)) {
            throw new \common_exception_MissingParameter(self::RESOURCE_PARAMETER, __CLASS__);
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
            $data['errorCode'] = 422;
            $data['errorMsg'] = 'Some fields are invalids';
            $data['version'] = TAO_VERSION;
        }

        echo $this->encode($data);
        exit(0);
    }

}