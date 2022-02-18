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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2022 (original work) Open Assessment Technologies SA.
 */

use oat\generis\model\OntologyRdfs;
use oat\generis\model\user\UserRdf;
use Psr\Container\ContainerInterface;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\ApplicationHelper;
use oat\tao\model\controller\SignedFormInstance;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\generis\model\user\PasswordConstraintsService;
use oat\tao\helpers\form\Feeder\SanitizerValidationFeeder;
use oat\tao\helpers\form\Feeder\SanitizerValidationFeederInterface;

/**
 * This container initialize the user edition form.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
class tao_actions_form_Users extends SignedFormInstance
{
    /** @var core_kernel_classes_Resource */
    protected $user;

    /** @var string */
    protected $formName = '';

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     *
     * @param  core_kernel_classes_Class $clazz
     * @param  core_kernel_classes_Resource $user
     * @param  boolean $forceAdd
     * @param array $options
     *
     * @throws common_exception_Error
     */
    public function __construct(
        core_kernel_classes_Class $clazz,
        core_kernel_classes_Resource $user = null,
        $forceAdd = false,
        $options = []
    ) {
        if (empty($clazz)) {
            throw new Exception('Set the user class in the parameters');
        }

        $this->formName = 'user_form';

        $service = tao_models_classes_UserService::singleton();
        if (!empty($user)) {
            $this->user = $user;
            $options['mode'] = 'edit';
        } else {
            if (isset($_POST[$this->formName . '_sent']) && isset($_POST['uri'])) {
                $this->user = new core_kernel_classes_Resource(tao_helpers_Uri::decode($_POST['uri']));
            } else {
                $this->user = $service->createInstance($clazz, $service->createUniqueLabel($clazz));
            }
            $options['mode'] = 'add';
        }

        if ($forceAdd) {
            $options['mode'] = 'add';
        }

        $userLangService = \oat\oatbox\service\ServiceManager::getServiceManager()->get(UserLanguageServiceInterface::class);
        if (!$userLangService->isDataLanguageEnabled()) {
            $options['excludedProperties'][] = UserRdf::PROPERTY_DEFLG;
        }

        $options['topClazz'] = UserRdf::CLASS_URI;

        parent::__construct($clazz, $this->user, $options);
    }

    /**
     * Short description of method getUser
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return core_kernel_classes_Resource
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Short description of method initForm
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        parent::initForm();

        $this->form->setName($this->formName);

        $actions = tao_helpers_form_FormFactory::getCommonActions('top');
        $this->form->setActions($actions, 'top');
        $this->form->setActions($actions, 'bottom');
    }

    /**
     * Short description of method initElements
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     */
    protected function initElements()
    {
        if (!isset($this->options['mode'])) {
            throw new Exception('Please set a mode into container options');
        }

        parent::initElements();

        $this->initLoginElement();

        //set default lang to the languages fields
        $langService = tao_models_classes_LanguageService::singleton();
        $userLangService = \oat\oatbox\service\ServiceManager::getServiceManager()->get(UserLanguageServiceInterface::class);
        if ($userLangService->isDataLanguageEnabled()) {
            $dataLangElt = $this->form->getElement(tao_helpers_Uri::encode(UserRdf::PROPERTY_DEFLG));
            $dataLangElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
            $dataUsage = new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA);
            $dataOptions = [];
            foreach ($langService->getAvailableLanguagesByUsage($dataUsage) as $lang) {
                $dataOptions[tao_helpers_Uri::encode($lang->getUri())] = $lang->getLabel();
            }
            $dataLangElt->setOptions($dataOptions);
        }

        $uiLangElt = $this->form->getElement(tao_helpers_Uri::encode(UserRdf::PROPERTY_UILG));
        $uiLangElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        $guiUsage = new core_kernel_classes_Resource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_GUI);
        $guiOptions = [];
        foreach ($langService->getAvailableLanguagesByUsage($guiUsage) as $lang) {
            $guiOptions[tao_helpers_Uri::encode($lang->getUri())] = $lang->getLabel();
        }
        $uiLangElt->setOptions($guiOptions);

        // roles field
        $property = new core_kernel_classes_Property(UserRdf::PROPERTY_ROLES);
        $roles = $property->getRange()->getInstances(true);
        $rolesOptions = [];
        foreach ($roles as $r) {
            $rolesOptions[tao_helpers_Uri::encode($r->getUri())] = $r->getLabel();
        }
        asort($rolesOptions);

        $userService = tao_models_classes_UserService::singleton();
        $rolesOptions = $userService->getPermittedRoles($userService->getCurrentUser(), $rolesOptions);

        $rolesElt = $this->form->getElement(tao_helpers_Uri::encode($property->getUri()));
        $rolesElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
        $rolesElt->setOptions($rolesOptions);

        // password field
        $this->form->removeElement(tao_helpers_Uri::encode(UserRdf::PROPERTY_PASSWORD));

        if ($this->options['mode'] === 'add') {
            $pass1Element = tao_helpers_form_FormFactory::getElement('password1', 'Hiddenbox');
            $pass1Element->setDescription(__('Password'));
            $pass1Element->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
            $pass1Element->addValidators(PasswordConstraintsService::singleton()->getValidators());
            $pass1Element->setBreakOnFirstError(false);

            $this->form->addElement($pass1Element);

            $pass2Element = tao_helpers_form_FormFactory::getElement('password2', 'Hiddenbox');
            $pass2Element->setDescription(__('Repeat password'));
            $pass2Element->addValidators([
                tao_helpers_form_FormFactory::getValidator('NotEmpty'),
                tao_helpers_form_FormFactory::getValidator('Password', ['password2_ref' => $pass1Element]),
            ]);
            $this->form->addElement($pass2Element);
        } else {
            if (ApplicationHelper::isDemo()) {
                $warning  = tao_helpers_form_FormFactory::getElement('warningpass', 'Label');
                $warning->setValue(__('Unable to change passwords in demo mode'));
                $this->form->addElement($warning);
                $this->form->createGroup("pass_group", __("Change the password"), ['warningpass']);
            } else {
                $pass2Element = tao_helpers_form_FormFactory::getElement('password2', 'Hiddenbox');
                $pass2Element->setDescription(__('New password'));
                $pass2Element->addValidators(PasswordConstraintsService::singleton()->getValidators());
                $pass2Element->setBreakOnFirstError(false);
                $this->form->addElement($pass2Element);

                $pass3Element = tao_helpers_form_FormFactory::getElement('password3', 'Hiddenbox');
                $pass3Element->setDescription(__('Repeat new password'));
                $pass3Element->addValidators([
                    tao_helpers_form_FormFactory::getValidator('Password', ['password2_ref' => $pass2Element]),
                ]);
                $this->form->addElement($pass3Element);

                $this->form->createGroup("pass_group", __("Change the password"), ['password2', 'password3']);
                if (empty($_POST[$pass2Element->getName()]) && empty($_POST[$pass3Element->getName()])) {
                    $pass2Element->setForcedValid();
                    $pass3Element->setForcedValid();
                }
            }
        }

        $this->getSanitizerValidationFeeder()
            ->addFormElement($this->form, OntologyRdfs::RDFS_LABEL)
            ->addFormElement($this->form, UserRdf::PROPERTY_LOGIN)
            ->addFormElement($this->form, UserRdf::PROPERTY_FIRSTNAME)
            ->addFormElement($this->form, UserRdf::PROPERTY_LASTNAME)
            ->feed();
    }

    private function initLoginElement(): void
    {
        /** @var tao_helpers_form_FormElement $element */
        $element = $this->form->getElement(tao_helpers_Uri::encode(UserRdf::PROPERTY_LOGIN));

        $element->feedInputValue();
        $value = $element->getInputValue() ?? $element->getRawValue();

        if ($this->options['mode'] !== 'add' && $this->getSanitizerRegexValidator()->evaluate($value)) {
            $element->setAttributes(
                [
                    'readonly' => 'readonly',
                    'disabled' => 'disabled',
                ]
            );

            return;
        }

        $element->addValidators([
            tao_helpers_form_FormFactory::getValidator('NotEmpty'),
            tao_helpers_form_FormFactory::getValidator(
                'Callback',
                [
                    'object' => tao_models_classes_UserService::singleton(),
                    'method' => 'loginAvailable',
                    'message' => __('This Login is already in use'),
                ]
            )
        ]);
    }

    private function getSanitizerValidationFeeder(): SanitizerValidationFeederInterface
    {
        return $this->getContainer()->get(SanitizerValidationFeeder::USER_FORM_SERVICE_ID);
    }

    private function getSanitizerRegexValidator(): tao_helpers_form_Validator
    {
        return $this->getContainer()->get(tao_helpers_form_validators_Regex::USER_FORM_SERVICE_ID);
    }

    private function getContainer(): ContainerInterface
    {
        return ServiceManager::getServiceManager()->getContainer();
    }
}
