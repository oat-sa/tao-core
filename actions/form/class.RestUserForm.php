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

use \oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use \oat\generis\model\user\PasswordConstraintsService;
use \oat\oatbox\validator\ValidatorInterface;
use \Zend\ServiceManager\ServiceLocatorAwareTrait;
use \Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Class tao_actions_form_RestUserForm
 *
 * Implementation of tao_actions_form_RestForm to manage generis user forms for edit and create
 */
class tao_actions_form_RestUserForm extends tao_actions_form_RestForm implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var bool If password is change, set it to true to save new one */
    protected $changePassword = false;

    /**
     * Get class properties, remove password field to not expose it.
     *
     * @return array
     */
    protected function getClassProperties()
    {
        $properties = parent::getClassProperties();
        unset($properties[PROPERTY_USER_PASSWORD]);
        return $properties;
    }

    /**
     * Get the form data.
     * Set readOnly to login in case of edition.
     * Add password and password confirmation with different label depending creation or edition
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();
        $properties = $data[self::PROPERTIES];

        foreach ($properties as $property) {
            if ($this->isEdition() && $property['uri'] == 'http://www.tao.lu/Ontologies/generis.rdf#login') {
                $property['widget'] = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Readonly';
            }
        }

        if ($this->isCreation()) {
            $properties[] = [
                'uri' => 'password1',
                'label' => __('Password'),
                'widget' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox',
            ];

            $properties[] = [
                'uri' => 'password2',
                'label' => __('Repeat password'),
                'widget' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox',
            ];
        } else {
            $properties[] = [
                'uri' => 'password1',
                'label' => __('New password'),
                'widget' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox',
            ];

            $properties[] = [
                'uri' => 'password2',
                'label' => __('Repeat new password'),
                'widget' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HiddenBox',
            ];
        }

        $data[self::PROPERTIES] = $properties;
        return $data;
    }

    /**
     * Bind data from parameters to form. Extract password and confirmation.
     *
     * @param array $parameters
     * @return $this
     */
    public function bind(array $parameters = [])
    {
        parent::bind($parameters);

        if (isset($parameters['password1'])) {
            $this->formProperties['password1'] = [
                'uri' => 'password1',
                'formValue' => $parameters['password1'],
            ];
        }
        if (isset($parameters['password2'])) {
            $this->formProperties['password2'] = [
                'uri' => 'password2',
                'formValue' => $parameters['password2'],
            ];
        }

        return $this;
    }

    /**
     * Validate the form against the property validators.
     * In case of range, check if value belong to associated ranges list
     *
     * @return common_report_Report
     */
    public function validate()
    {
        $report = parent::validate();

        // Validate passwords
        $password1 = isset($this->formProperties['password1']['formValue'])
            ? $this->formProperties['password1']['formValue']
            : null;
        $password2 = isset($this->formProperties['password2']['formValue'])
            ? $this->formProperties['password2']['formValue']
            : null;

        if (!is_null($password1) || !is_null($password2)) {
            try {
                $this->validatePassword($password1);
                if ($password1 != $password2) {
                    throw new common_exception_ValidationFailed('password', __('Passwords do not match'));
                }
                $this->changePassword = true;
            } catch (common_exception_ValidationFailed $e) {
                $subReport = common_report_Report::createFailure('Password: ' . $e->getMessage());
                $subReport->setData('password');
                $report->add($subReport);
            }
        }

        // Validate new login availability
        if ($this->isCreation()) {
            foreach($this->formProperties as $property) {
                if (
                    $property['uri'] == 'http://www.tao.lu/Ontologies/generis.rdf#login'
                    && !$this->isLoginAvailable($property['formValue'])
                ) {
                    $subReport = common_report_Report::createFailure(__('Login is already in use.'));
                    $subReport->setData($property['uri']);
                    $report->add($subReport);
                }
            }
        }

        return $report;
    }

    /**
     * Get validators of a property.
     * Add not empty validator to user to languages and roles properties
     *
     * @param core_kernel_classes_Property $property
     * @return array
     */
    protected function getPropertyValidators(core_kernel_classes_Property $property)
    {
        $validators = parent::getPropertyValidators($property);

        $notEmptyProperties = [
            PROPERTY_USER_DEFLG, PROPERTY_USER_UILG, PROPERTY_USER_ROLES
        ];

        if (in_array($property->getUri(), $notEmptyProperties)) {
            $validators[] = 'notEmpty';
        }

        return $validators;
    }

    /**
     * Validate password by evaluate it against PasswordConstraintService and NotEmpty validators
     *
     * @param $password
     * @throws common_exception_ValidationFailed If invalid
     */
    protected function validatePassword($password)
    {
        if (!(new tao_helpers_form_validators_NotEmpty())->evaluate($password)) {
            throw new common_exception_ValidationFailed('password', __('Password is empty.'));
        }

        /** @var ValidatorInterface $validator */
        foreach (PasswordConstraintsService::singleton()->getValidators() as $validator) {
            if (!$validator->evaluate($password)) {
                throw new common_exception_ValidationFailed('password', $validator->getMessage());
            }
        }
    }

    /**
     * Prepare form properties values to be saved.
     * Remove form fields for password and create a new one with encrypted value.
     *
     * @return array
     */
    protected function prepareValuesToSave()
    {
        $values = parent::prepareValuesToSave();
        if ($this->changePassword) {
            $values[PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()
                ->encrypt($this->formProperties['password2']['formValue']);
        }
        unset($values['password1']);
        unset($values['password2']);

        return $values;
    }

    /**
     * Check if login is already used. Return false if not, yes if a User resource has he same login
     *
     * @param $login
     * @return bool
     */
    protected function isLoginAvailable($login)
    {
        /** @var ComplexSearchService $search */
        $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
        $queryBuilder = $search->query();
        $query = $search->searchType($queryBuilder , $this->getUserClass() , true)
            ->add('http://www.tao.lu/Ontologies/generis.rdf#login')->equals($login)
        ;

        $queryBuilder->setCriteria($query);
        $result = $search->getGateway()->search($queryBuilder);

        return $result->count() > 0;
    }

    /**
     * Get the class associated to current form
     *
     * @return core_kernel_classes_Class
     */
    protected function getTopClass()
    {
        return $this->getUserClass();
    }

    /**
     * Get the generis user class
     *
     * @return core_kernel_classes_Class
     */
    protected function getUserClass()
    {
        return $this->getClass(CLASS_GENERIS_USER);
    }
}