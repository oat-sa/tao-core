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
     * Get the form data.
     * Set readOnly to login in case of edition.
     * Add password and password confirmation with different label depending creation or edition
     *
     * @return array
     */
    public function getData()
    {
        $properties = $this->formProperties;

        foreach ($properties as $property) {
            if ($this->isEdition() && $property['uri'] == 'http://www.tao.lu/Ontologies/generis.rdf#login') {
                $property['widget'] = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Readonly';
                break;
            }
        }

        if ($this->isEdition()) {
            foreach ($properties as $key => $property) {
                if ($property['uri'] == PROPERTY_USER_PASSWORD && isset($property['value'])) {
                    $properties[$key]['value'] = '';
                    break;
                }
            }
        }

        return [
            self::PROPERTIES => $properties,
            self::RANGES => $this->ranges,
        ];
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

        $password = null;
        foreach ($this->formProperties as $key => $property) {
            if ($property['uri'] == PROPERTY_USER_PASSWORD && isset($this->formProperties[$key]['formValue'])) {
                $password = $this->formProperties[$key]['formValue'];
                break;
            }
        }

        if ($this->isCreation() || ($this->isEdition() && !is_null($password))) {
            try {
                $this->validatePassword($password);
                $this->changePassword = true;
            } catch (common_exception_ValidationFailed $e) {
                $subReport = common_report_Report::createFailure('Password: ' . $e->getMessage());
                $subReport->setData(PROPERTY_USER_PASSWORD);
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
            throw new common_exception_ValidationFailed(PROPERTY_USER_PASSWORD, __('Password is empty.'));
        }

        /** @var ValidatorInterface $validator */
        foreach (PasswordConstraintsService::singleton()->getValidators() as $validator) {
            if (!$validator->evaluate($password)) {
                throw new common_exception_ValidationFailed(PROPERTY_USER_PASSWORD, $validator->getMessage());
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
            $password = null;
            foreach ($this->formProperties as $key => $property) {
                if ($property['uri'] == PROPERTY_USER_PASSWORD && isset($this->formProperties[$key]['formValue'])) {
                    $password = $this->formProperties[$key]['formValue'];
                    break;
                }
            }

            $values[PROPERTY_USER_PASSWORD] = core_kernel_users_Service::getPasswordHash()->encrypt($password);
        }

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
        return \tao_models_classes_UserService::singleton()->loginAvailable($login);
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