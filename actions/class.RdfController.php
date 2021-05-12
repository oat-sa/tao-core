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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2021 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\oatbox\user\User;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\accessControl\ActionAccessControl;
use oat\tao\model\accessControl\PermissionChecker;
use oat\tao\model\controller\SignedFormInstance;
use oat\tao\model\lock\LockManager;
use oat\tao\model\menu\ActionService;
use oat\tao\model\menu\MenuService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\resources\ResourceService;
use oat\tao\model\security\SecurityException;
use oat\tao\model\security\SignatureGenerator;
use oat\tao\model\security\SignatureValidator;
use tao_helpers_form_FormContainer as FormContainer;

/**
 * The TaoModule is an abstract controller,
 * the tao children extensions Modules should extends the TaoModule to beneficiate the shared methods.
 * It regroups the methods that can be applied on any extension (the rdf:Class managment for example)
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao

 */
abstract class tao_actions_RdfController extends tao_actions_CommonModule
{
    use OntologyAwareTrait;

    /** @var SignatureValidator */
    protected $signatureValidator;

    public function __construct()
    {
        // maybe some day we can inject it
        $this->signatureValidator = new SignatureValidator();

        parent::__construct();
    }

    /**
     * The Modules access the models throught the service instance
     *
     * @var tao_models_classes_Service
     */
    protected $service = null;

    /**
     * @return tao_models_classes_ClassService
     */
    protected function getClassService()
    {
        if (is_null($this->service)) {
            throw new common_exception_Error('No service defined for ' . get_called_class());
        }
        return $this->service;
    }

    /**
     * @param string $uri
     *
     * @throws common_exception_Error
     * @throws SecurityException
     */
    protected function validateInstanceRoot($uri)
    {
        $instance = $this->getResource($uri);

        $root = $this->getRootClass();

        if ($instance->isClass()) {
            $class = new core_kernel_classes_Class($instance->getUri());

            if (!($class->isSubClassOf($root) || $class->equals($root))) {
                throw new SecurityException(
                    sprintf(
                        'Security issue: class %s is not a subclass of %s',
                        $instance->getLabel(),
                        $root->getLabel()
                    )
                );
            }

            return;
        }

        if (!$instance->isInstanceOf($root)) {
            throw new SecurityException(sprintf(
                'Security issue: instance %s is not a child of %s',
                $instance->getLabel(),
                $root->getLabel()
            ));
        }
    }

    protected function validateInstancesRoot($uris)
    {
        foreach ($uris as $uri) {
            $this->validateInstanceRoot($uri);
        }
    }

    /**
     * If you want strictly to check if the resource is locked,
     * you should use LockManager::getImplementation()->isLocked($resource)
     * Controller level convenience method to check if @resource is being locked, prepare data ans sets view,
     *
     * @param core_kernel_classes_Resource $resource
     * @param $view
     *
     * @return boolean
     */
    protected function isLocked($resource, $view = null)
    {

        $lock = LockManager::getImplementation()->getLockData($resource);
        if (!is_null($lock) && $lock->getOwnerId() != $this->getSession()->getUser()->getIdentifier()) {
            //if (LockManager::getImplementation()->isLocked($resource)) {
            $params = [
                'id' => $resource->getUri(),
                'topclass-label' => $this->getRootClass()->getLabel()
            ];
            if (!is_null($view)) {
                $params['view'] = $view;
                $params['ext'] = Context::getInstance()->getExtensionName();
            }
            $this->forward('locked', 'Lock', 'tao', $params);
        }
        return false;
    }

    /**
     * get the current item class regarding the classUri' request parameter
     * @return core_kernel_classes_Class the item class
     * @throws Exception
     */
    protected function getCurrentClass()
    {
        $classUri = tao_helpers_Uri::decode($this->getRequestParameter('classUri'));
        if (is_null($classUri) || empty($classUri)) {
            $class = null;
            $resource = $this->getCurrentInstance();
            foreach ($resource->getTypes() as $type) {
                $class = $type;
                break;
            }
            if (is_null($class)) {
                throw new Exception("No valid class uri found");
            }
            $returnValue = $class;
        } else {
            if (!common_Utils::isUri($classUri)) {
                throw new tao_models_classes_MissingRequestParameterException('classUri - expected to be valid URI');
            }
            $returnValue = $this->getClass($classUri);
        }

        return $returnValue;
    }

    /**
     *  ! Please override me !
     * get the current instance regarding the uri and classUri in parameter
     * @param string $parameterName
     *
     * @return core_kernel_classes_Resource
     * @throws tao_models_classes_MissingRequestParameterException
     */
    protected function getCurrentInstance($parameterName = 'uri')
    {
        $uri = tao_helpers_Uri::decode($this->getRequestParameter($parameterName));

        $this->validateUri($uri);

        return $this->getResource($uri);
    }

    protected function validateUri($uri)
    {
        if (!common_Utils::isUri($uri)) {
            throw new tao_models_classes_MissingRequestParameterException('uri');
        }
    }

    /**
     * get the main class
     * @return core_kernel_classes_Class
     */
    abstract protected function getRootClass();

    public function editClassProperties()
    {
        return $this->forward('index', 'PropertiesAuthoring', 'tao');
    }

    /**
     * Deprecated alias for getClassForm
     *
     * @deprecated
     */
    protected function editClass(core_kernel_classes_Class $class, core_kernel_classes_Resource $resource, core_kernel_classes_Class $topclass = null)
    {
        return $this->getClassForm($class, $resource, $topclass);
    }

    protected function getClassForm($class, $resource, $topclass = null)
    {
        $controller = new tao_actions_PropertiesAuthoring();
        $controller->setServiceLocator($this->getServiceLocator());
        return $controller->getClassForm($class);
    }

    /*
     * Actions
     */

    /**
     * Main action
     * @return void
     */
    public function index()
    {
        $this->setView('index.tpl');
    }

    /**
     * Renders json data from the current ontology root class.
     *
     * The possible request parameters are the following:
     *
     * * uniqueNode: A URI indicating the returned hiearchy will be a single class, with a single children corresponding to the URI.
     * * browse:
     * * hideInstances:
     * * chunk:
     * * offset:
     * * limit:
     * * subclasses:
     * * classUri:
     *
     * @return void
     * @requiresRight classUri READ
     */
    public function getOntologyData()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_IsAjaxAction(__FUNCTION__);
        }
        $options = $this->getTreeOptionsFromRequest([]);

        //generate the tree from the given parameters
        $tree = $this->getClassService()->toTree($options['class'], $options);

        //retrieve resources permissions
        $user = \common_Session_SessionManager::getSession()->getUser();
        $permissions = $this->getResourceService()->getResourcesPermissions($user, $tree);

        //expose the tree
        $this->returnJson([
            'tree' => $tree,
            'permissions' => $permissions
        ]);
    }

    /**
     * Get options to generate tree
     * @return array
     * @throws Exception
     */
    protected function getTreeOptionsFromRequest($options = [])
    {
        $options = array_merge([
            'subclasses' => true,
            'instances' => true,
            'highlightUri' => '',
            'chunk' => false,
            'offset' => 0,
            'limit' => 0
        ], $options);

        if ($this->hasRequestParameter('loadNode')) {
            $options['uniqueNode'] = $this->getRequestParameter('loadNode');
        }

        if ($this->hasRequestParameter("selected")) {
            $options['browse'] = [$this->getRequestParameter("selected")];
        }

        if ($this->hasRequestParameter('hideInstances')) {
            if ((bool) $this->getRequestParameter('hideInstances')) {
                $options['instances'] = false;
            }
        }
        if ($this->hasRequestParameter('classUri')) {
            $options['class'] = $this->getCurrentClass();
            $options['chunk'] = !$options['class']->equals($this->getRootClass());
        } else {
            $options['class'] = $this->getRootClass();
        }

        if ($this->hasRequestParameter('offset')) {
            $options['offset'] = $this->getRequestParameter('offset');
        }

        if ($this->hasRequestParameter('limit')) {
            $options['limit'] = $this->getRequestParameter('limit');
        }

        if ($this->hasRequestParameter('order')) {
            $options['order'] = tao_helpers_Uri::decode($this->getRequestParameter('order'));
        }

        if ($this->hasRequestParameter('orderdir')) {
            $options['orderdir'] = $this->getRequestParameter('orderdir');
        }
        return $options;
    }

    /**
     * Add permission information to the tree structure
     *
     * @deprecated
     *
     * @param array $tree
     * @return array
     */
    protected function addPermissions($tree)
    {
        $user = $this->getSession()->getUser();

        $section = MenuService::getSection(
            $this->getRequestParameter('extension'),
            $this->getRequestParameter('perspective'),
            $this->getRequestParameter('section')
        );

        $actions = $section->getActions();

        //then compute ACL for each node of the tree
        $treeKeys = array_keys($tree);
        if (isset($treeKeys[0]) && is_int($treeKeys[0])) {
            foreach ($tree as $index => $treeNode) {
                $tree[$index] = $this->computePermissions($actions, $user, $treeNode);
            }
        } else {
            $tree = $this->computePermissions($actions, $user, $tree);
        }

        return $tree;
    }

    /**
     * compulte permissions for a node against actions
     *
     * @deprecated
     *
     * @param array[] $actions the actions data with context, name and the resolver
     * @param User $user the user
     * @param array $node a tree node
     * @return array the node augmented with permissions
     */
    private function computePermissions($actions, $user, $node)
    {
        if (isset($node['attributes']['data-uri'])) {
            if ($node['type'] == 'class') {
                $params = ['classUri' => $node['attributes']['data-uri']];
            } else {
                $params = [];
                foreach ($node['attributes'] as $key => $value) {
                    if (substr($key, 0, strlen('data-')) == 'data-') {
                        $params[substr($key, strlen('data-'))] = $value;
                    }
                }
            }
            $params['id'] = $node['attributes']['data-uri'];

            $node['permissions'] = $this->getActionService()->computePermissions($actions, $user, $params);
        }
        if (isset($node['children'])) {
            foreach ($node['children'] as $index => $child) {
                $node['children'][$index] = $this->computePermissions($actions, $user, $child);
            }
        }
        return $node;
    }

    /**
     * Common action to view and change the label of a class
     */
    public function editClassLabel()
    {
        $class = $this->getCurrentClass();
        $signature = $this->createFormSignature();

        $classUri = $class->getUri();
        $hasWriteAccess = $this->hasWriteAccess($classUri) && $this->hasWriteAccessToAction('editClassLabel');

        $editClassLabelForm = new tao_actions_form_EditClassLabel(
            $class,
            $this->getRequestParameters(),
            $signature,
            [FormContainer::CSRF_PROTECTION_OPTION => true, FormContainer::IS_DISABLED => !$hasWriteAccess]
        );

        $myForm = $editClassLabelForm->getForm();

        if ($myForm->isSubmited() && $myForm->isValid()) {
            if ($hasWriteAccess) {
                $class->setLabel($myForm->getValue(tao_helpers_Uri::encode(OntologyRdfs::RDFS_LABEL)));
                $this->setData('message', __('%s Class saved', $class->getLabel()));
            } else {
                $this->setData('errorMessage', __('You do not have the required rights to edit this resource.'));
            }

            $this->setData('selectNode', tao_helpers_Uri::encode($classUri));
            $this->setData('reload', true);
        }

        $this->setData('formTitle', __('Edit class %s', \tao_helpers_Display::htmlize($class->getLabel())));
        $this->setData('myForm', $myForm->render());
        $this->setView('form.tpl', 'tao');
    }

    /**
     * Add an instance of the selected class
     * @requiresRight id WRITE
     *
     * @throws SecurityException
     * @throws InconsistencyConfigException
     * @throws common_exception_BadRequest
     * @throws common_exception_Error
     */
    public function addInstance()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $id = $this->getRequestParameter('id');

        $this->validateInstanceRoot($id);

        try {
            $this->validateCsrf();
        } catch (common_exception_Unauthorized $e) {
            $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
            return;
        }

        $this->signatureValidator->checkSignature(
            $this->getRequestParameter('signature'),
            $id
        );

        $response = [];

        $class = $this->getClass($id);
        $label = $this->getClassService()->createUniqueLabel($class);

        $instance = $this->getClassService()->createInstance($class, $label);

        if ($instance instanceof core_kernel_classes_Resource) {
            $response = [
                'success' => true,
                'label' => $instance->getLabel(),
                'uri'   => $instance->getUri()
            ];
        }

        $this->returnJson($response);
    }

    /**
     * Add a subclass to the currently selected class
     * @requiresRight id WRITE
     * @throws Exception
     * @throws common_exception_BadRequest
     */
    public function addSubClass()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $classId = $this->getRequestParameter('id');

        $this->signatureValidator->checkSignature(
            $this->getRequestParameter('signature'),
            $classId
        );

        try {
            $this->validateCsrf();
        } catch (common_exception_Unauthorized $e) {
            $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
            return;
        }

        $this->validateInstanceRoot($classId);

        $parent = $this->getClass($classId);
        $class = $this->getClassService()->createSubClass($parent);
        if ($class instanceof core_kernel_classes_Class) {
            $this->returnJson([
                'success' => true,
                'label' => $class->getLabel(),
                'uri'   => tao_helpers_Uri::encode($class->getUri())
            ]);
        }
    }

    /**
     * Add an instance of the selected class
     * @throws common_exception_BadRequest
     * @return void
     */
    public function addInstanceForm()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $class = $this->getCurrentClass();
        $formContainer = new tao_actions_form_CreateInstance([$class],
            [
                 FormContainer::CSRF_PROTECTION_OPTION => true,
                 FormContainer::ADDITIONAL_VALIDATORS => $this->getExtraValidationRules(),
                 tao_actions_form_CreateInstance::EXCLUDED_PROPERTIES => $this->getExcludedProperties(),
            ]
        );

        $addInstanceForm = $formContainer->getForm();

        if ($addInstanceForm->isSubmited() && $addInstanceForm->isValid()) {
            $properties = $addInstanceForm->getValues();
            $instance = $this->createInstance([$class], $properties);

            $this->setData('message', __('%s created', $instance->getLabel()));
            $this->setData('reload', true);
        }

        $this->setData('formTitle', __('Create instance of ') . $class->getLabel());
        $this->setData('myForm', $addInstanceForm->render());

        $this->setView('form.tpl', 'tao');
    }

    /**
     * creates the instance
     *
     * @param array $classes
     * @param array $properties
     * @return core_kernel_classes_Resource
     */
    protected function createInstance($classes, $properties)
    {
        $first = array_shift($classes);
        $instance = $first->createInstanceWithProperties($properties);
        foreach ($classes as $class) {
            $instance = $this->getResource('');
            $instance->setType($class);
        }
        return $instance;
    }

    public function editInstance()
    {
        $class = $this->getCurrentClass();
        $instance = $this->getCurrentInstance();
        $myFormContainer = new SignedFormInstance(
            $class,
            $instance,
            [
                FormContainer::CSRF_PROTECTION_OPTION => true,
                FormContainer::ADDITIONAL_VALIDATORS => $this->getExtraValidationRules(),
                tao_actions_form_Instance::EXCLUDED_PROPERTIES => $this->getExcludedProperties()
            ]
        );

        $myForm = $myFormContainer->getForm();
        if ($myForm->isSubmited() && $myForm->isValid()) {
            $values = $myForm->getValues();
            // save properties
            $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($instance);
            $binder->bind($values);
            $message = __('Instance saved');

            $this->setData('message', $message);
            $this->setData('reload', true);
        }

        $this->setData('formTitle', __('Edit Instance'));
        $this->setData('myForm', $myForm->render());
        $this->setView('form.tpl', 'tao');
    }

    /**
     * Duplicate the current instance
     * render a JSON response
     *
     * @throws \League\Flysystem\FileExistsException
     * @throws common_Exception
     * @throws common_exception_BadRequest
     * @throws common_exception_Error
     * @throws tao_models_classes_MissingRequestParameterException
     *
     * @return void
     * @requiresRight uri READ
     * @requiresRight classUri WRITE
     */
    public function cloneInstance()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $uri = $this->getRequestParameter('uri');

        $this->signatureValidator->checkSignature(
            $this->getRequestParameter('signature'),
            $uri
        );

        try {
            $this->validateCsrf();
        } catch (common_exception_Unauthorized $e) {
            $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
            return;
        }
        $this->validateInstanceRoot($uri);

        $clone = $this->getClassService()->cloneInstance($this->getCurrentInstance(), $this->getCurrentClass());
        if ($clone !== null) {
            $this->returnJson([
                'success' => true,
                'message' => __('Successfully cloned instance as %s', $clone->getLabel()),
                'label' => $clone->getLabel(),
                'uri'   => tao_helpers_Uri::encode($clone->getUri())
            ]);
        }
    }

    /**
     * Copy a resource to a destination
     *
     * @requiresRight uri READ
     */
    public function copyInstance()
    {
        if (
            $this->hasRequestParameter('destinationClassUri')
            && $this->hasRequestParameter('uri')
            && common_Utils::isUri($this->getRequestParameter('destinationClassUri'))
        ) {
            try {
                $this->validateCsrf();
            } catch (common_exception_Unauthorized $e) {
                $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
                return;
            }
            $this->validateInstanceRoot(
                $this->getRequestParameter('uri')
            );

            $this->signatureValidator->checkSignature(
                $this->getRequestParameter('signature'),
                $this->getRequestParameter('uri')
            );

            $instance  = $this->getCurrentInstance();
            $destinationClass = $this->getClass($this->getRequestParameter('destinationClassUri'));

            if ($this->hasWriteAccess($destinationClass->getUri())) {
                $copy = $this->getClassService()->cloneInstance($instance, $destinationClass);

                if (!is_null($copy)) {
                    return $this->returnJson([
                        'success'  => true,
                        'data' => [
                            'label' => $copy->getLabel(),
                            'uri'   => $copy->getUri()
                        ]
                    ]);
                }
                return $this->returnJson([
                    'success'  => false,
                    'errorCode' => 204,
                    'errorMessage' =>  __("Unable to copy the resource")
                ], 204);
            }
            return $this->returnJson([
                'success'  => false,
                'errorCode' => 401,
                'errorMessage' =>  __("Permission denied to write in the selected class")
            ], 401);
        }
        return $this->returnJson([
            'success' => false,
            'errorCode' => 412,
            'errorMessage' => __('Missing Parameters')
        ], 412);
    }

    /**
     * Move an instance from a class to another
     * @return void
     * @requiresRight uri WRITE
     * @requiresRight destinationClassUri WRITE
     */
    public function moveInstance()
    {
        $response = [];
        if ($this->hasRequestParameter('destinationClassUri') && $this->hasRequestParameter('uri')) {
            $id = $this->getRequestParameter('uri');
            try {
                $this->validateCsrf();
            } catch (common_exception_Unauthorized $e) {
                $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
                return;
            }

            $this->validateInstanceRoot($id);

            $this->signatureValidator->checkSignature($this->getRequestParameter('signature'), $id);

            $instance = $this->getResource($id);
            $types = $instance->getTypes();
            $class = reset($types);
            $destinationUri = tao_helpers_Uri::decode($this->getRequestParameter('destinationClassUri'));
            $this->validateDestinationClass($destinationUri, $class->getUri());
            $destinationClass = $this->getClass($destinationUri);
            $confirmed = $this->getRequestParameter('confirmed');
            if (empty($confirmed) || $confirmed == 'false' || $confirmed ===  false) {
                $diff = $this->getClassService()->getPropertyDiff($class, $destinationClass);
                if (count($diff) > 0) {
                    return $this->returnJson([
                        'status'        => 'diff',
                        'data'          => $diff
                    ]);
                }

                $status = $this->getClassService()->changeClass($instance, $destinationClass);
                $response = ['status'      => $status];
            }
        }
        $this->returnJson($response);
    }

    /**
     * Move a single resource to another class
     *
     * @requiresRight uri WRITE
     *
     * @throws common_exception_Error
     * @throws common_exception_MethodNotAllowed
     */
    public function moveResource()
    {
        try {
            if (!$this->hasRequestParameter('uri')) {
                throw new InvalidArgumentException('Resource uri must be specified.');
            }

            $data = $this->getRequestParameter('uri');
            $id = $data['id'];

            try {
                $this->validateCsrf();
            } catch (common_exception_Unauthorized $e) {
                $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
                return;
            }

            $this->validateUri($id);
            $this->validateInstanceRoot($id);

            $this->signatureValidator->checkSignature($data['signature'], $id);

            $ids = [$id];

            $this->validateMoveRequest();
            $response = $this->moveAllInstances($ids);
            $this->returnJson($response);
        } catch (\InvalidArgumentException $e) {
            $this->returnJsonError($e->getMessage());
        }
    }

    /**
     * Move all specififed resources to the given destination root class
     *
     * @throws common_exception_Error
     * @throws common_exception_MethodNotAllowed
     * @requiresRight id WRITE
     */
    public function moveAll()
    {
        try {
            if (!$this->hasRequestParameter('ids')) {
                throw new InvalidArgumentException('Resource ids must be specified.');
            }
            $ids = [];

            foreach ($this->getRequestParameter('ids') as $id) {
                $ids[] = $id['id'];
            }

            $this->validateInstancesRoot($ids);

            if (empty($ids)) {
                throw new InvalidArgumentException('No instances specified.');
            }

            $this->signatureValidator->checkSignatures(
                $this->getRequestParameter('ids')
            );

            $this->validateMoveRequest();

            $response = $this->moveAllInstances($ids);
            $this->returnJson($response);
        } catch (\InvalidArgumentException $e) {
            $this->returnJsonError($e->getMessage());
        }
    }

    /**
     * Render the form to translate a Resource instance
     * @return void
     * @throws common_exception_Error
     * @throws tao_models_classes_MissingRequestParameterException
     * @requiresRight id WRITE
     */
    public function translateInstance()
    {
        $instance = $this->getCurrentInstance();

        $formContainer = new tao_actions_form_Translate(
            $this->getCurrentClass(),
            $instance,
            [FormContainer::CSRF_PROTECTION_OPTION => true]
        );
        $myForm = $formContainer->getForm();

        if ($this->hasRequestParameter('target_lang')) {
            $targetLang = $this->getRequestParameter('target_lang');
            $availableLanguages = tao_helpers_I18n::getAvailableLangsByUsage(
                $this->getResource(tao_models_classes_LanguageService::INSTANCE_LANGUAGE_USAGE_DATA)
            );

            if (in_array($targetLang, $availableLanguages)) {
                $langElt = $myForm->getElement('translate_lang');
                $langElt->setValue($targetLang);
                $langElt->setAttribute('readonly', 'true');

                $trData = $this->getClassService()->getTranslatedProperties($instance, $targetLang);
                foreach ($trData as $key => $value) {
                    $element = $myForm->getElement(tao_helpers_Uri::encode($key));
                    if ($element !== null) {
                        $element->setValue($value);
                    }
                }
            }
        }

        if ($myForm->isSubmited() && $myForm->isValid()) {
            $values = $myForm->getValues();
            if (isset($values['translate_lang'])) {
                $datalang = $this->getSession()->getDataLanguage();
                $lang = $values['translate_lang'];

                $translated = 0;
                foreach ($values as $key => $value) {
                    if (0 === strpos($key, 'http')) {
                        $value = trim($value);
                        $property = $this->getProperty($key);
                        if (empty($value)) {
                            if ($datalang !== $lang && $lang !== '') {
                                $instance->removePropertyValueByLg($property, $lang);
                            }
                        } elseif ($instance->editPropertyValueByLg($property, $value, $lang)) {
                            $translated++;
                        }
                    }
                }
                if ($translated > 0) {
                    $this->setData('message', __('Translation saved'));
                }
            }
        }

        $this->setData('myForm', $myForm->render());
        $this->setData('formTitle', __('Translate'));
        $this->setView('form.tpl', 'tao');
    }

    /**
     * load the translated data of an instance regarding the given lang
     *
     * @throws common_exception_BadRequest
     * @throws common_exception_Error
     * @throws tao_models_classes_MissingRequestParameterException
     *
     * @return void
     */
    public function getTranslatedData()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }
        $data = [];
        if ($this->hasRequestParameter('lang')) {
            $data = tao_helpers_Uri::encodeArray(
                $this->getClassService()->getTranslatedProperties(
                    $this->getCurrentInstance(),
                    $this->getRequestParameter('lang')
                ),
                tao_helpers_Uri::ENCODE_ARRAY_KEYS
            );
        }
        $this->returnJson($data);
    }

    /**
     * delete an instance or a class
     * called via ajax
     *
     * @throws common_exception_BadRequest
     * @throws common_exception_MissingParameter
     */
    public function delete()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        if ($this->hasRequestParameter('uri')) {
            return $this->forward('deleteResource', null, null, (['id' => tao_helpers_Uri::decode($this->getRequestParameter('uri'))]));
        } elseif ($this->hasRequestParameter('classUri')) {
            return $this->forward('deleteClass', null, null, (['id' => tao_helpers_Uri::decode($this->getRequestParameter('classUri'))]));
        } else {
            throw new common_exception_MissingParameter();
        }
    }

    /**
     * Generic resource deletion action
     *
     * @throws Exception
     * @throws common_exception_BadRequest
     * @requiresRight id WRITE
     */
    public function deleteResource()
    {
        if (!$this->isXmlHttpRequest() || !$this->hasRequestParameter('id')) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $id = $this->getRequestParameter('id');

        // Csrf token validation
        try {
            $this->validateCsrf();
        } catch (common_exception_Unauthorized $e) {
            $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
            return;
        }

        $this->validateInstanceRoot($id);

        $this->signatureValidator->checkSignature(
            $this->getRequestParameter('signature'),
            $id
        );

        $resource = $this->getResource($this->getRequestParameter('id'));
        $deleted = $this->getClassService()->deleteResource($resource);
        return $this->returnJson([
            'success' => $deleted,
            'message' => __('Successfully deleted %s', $resource->getLabel()),
            'deleted' => $deleted
        ]);
    }

    /**
     * Generic class deletion action
     *
     * @throws Exception
     * @throws common_exception_BadRequest
     * @requiresRight id WRITE
     */
    public function deleteClass()
    {
        if (!$this->isXmlHttpRequest() || !$this->hasRequestParameter('id')) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $id = $this->getRequestParameter('id');

        // Csrf token validation
        try {
            $this->validateCsrf();
        } catch (common_exception_Unauthorized $e) {
            $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
            return;
        }

        $this->validateInstanceRoot($id);

        $this->signatureValidator->checkSignature(
            $this->getRequestParameter('signature'),
            $id
        );

        $class = $this->getClass($id);
        if ($this->getRootClass()->equals($class)) {
            $success = false;
            $msg = __('You cannot delete the root node');
        } else {
            $label = $class->getLabel();
            $success = $this->getClassService()->deleteClass($class);
            $msg = $success ? __('%s has been deleted', $label) : __('Unable to delete %s', $label);
        }

        $this->returnJson([
            'success' => $success,
            'message' => $msg,
            'deleted' => $success
        ]);
    }

    /**
     * Delete all given resources
     *
     * @requiresRight ids WRITE
     *
     * @throws Exception
     * @throws common_exception_BadRequest
     */
    public function deleteAll()
    {
        $response = [
            'success' => true,
            'deleted' => []
        ];
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        // Csrf token validation
        try {
            $this->validateCsrf();
        } catch (common_exception_Unauthorized $e) {
            $this->response = $this->getPsrResponse()->withStatus(403, __('Unable to process your request'));
            return;
        }

        $ids = [];

        foreach ($this->getRequestParameter('ids') as $id) {
            $ids[] = $id['id'];
        }

        $this->signatureValidator->checkSignatures($this->getRequestParameter('ids'));

        $this->validateInstancesRoot($ids);

        foreach ($ids as $id) {
            $deleted = false;
            $deletedResourceLabel = '';
            try {
                if ($this->hasWriteAccess($id)) {
                    $resource = new \core_kernel_classes_Resource($id);
                    if ($resource->isClass()) {
                        $class = new \core_kernel_classes_Class($id);
                        $deletedResourceLabel = $class->getLabel();
                        $deleted = $this->getClassService()->deleteClass($class);
                    } else {
                        $deletedResourceLabel = $resource->getLabel();
                        $deleted = $this->getClassService()->deleteResource($resource);
                    }
                }
            } catch (\common_Exception $ce) {
                \common_Logger::w('Unable to remove resource ' . $id . ' : ' . $ce->getMessage());
            }
            if ($deleted) {
                $response['deleted'][] = $id;
            }
            $response['message'] = __('Successfully deleted %s', $deletedResourceLabel);
        }

        return $this->returnJson($response);
    }

    /**
     * Test whenever the current user has "WRITE" access to the specified id
     *
     * @param string $resourceId
     * @return boolean
     */
    protected function hasWriteAccess($resourceId)
    {
        /** @var PermissionChecker $permissionChecker */
        $permissionChecker = $this->getServiceLocator()->get(PermissionChecker::class);

        return $permissionChecker->hasWriteAccess($resourceId);
    }

    protected function hasWriteAccessToAction(string $action, ?User $user = null): bool
    {
        return $this->getActionAccessControl()->hasWriteAccess(static::class, $action, $user);
    }

    /**
     * Validate request with all required parameters
     *
     * @throws common_exception_Error
     * @throws common_exception_MethodNotAllowed If it is not POST method
     * @throws InvalidArgumentException If parameters are not correct
     */
    protected function validateMoveRequest()
    {
        if (!$this->isRequestPost()) {
            throw new common_exception_MethodNotAllowed('Only POST method is allowed to move instances.');
        }

        if (!$this->hasRequestParameter('destinationClassUri')) {
            throw new InvalidArgumentException('Destination class must be specified');
        }

        $destinationClass = new \core_kernel_classes_Class($this->getRequestParameter('destinationClassUri'));
        if (!$destinationClass->isClass()) {
            throw new InvalidArgumentException('Destination class must be a valid class');
        }
    }

    /**
     * Move instances to another class
     *
     * {
     *   "destinationClassUri": "http://test.it",
     *   "ids": [
     *     "http://resource1",
     *     "http://resource2",
     *     "http://class1",
     *     "http://class2"
     *   ]
     * }
     * @requiresRight destinationClassUri WRITE
     * @params array $ids The list of instance uris to move
     *
     * @throws common_exception_Error
     */
    protected function moveAllInstances(array $ids)
    {
        $rootClass = $this->getClassService()->getRootClass();

        if (in_array($rootClass->getUri(), $ids)) {
            throw new InvalidArgumentException(sprintf('Root class "%s" cannot be moved', $rootClass->getUri()));
        }

        $destinationClass = new \core_kernel_classes_Class($this->getRequestParameter('destinationClassUri'));

        if (!$destinationClass->isSubClassOf($rootClass) && $destinationClass->getUri() != $rootClass->getUri()) {
            throw new InvalidArgumentException(sprintf('Instance "%s" cannot be moved to another root class', $destinationClass->getUri()));
        }

        list($statuses, $instances, $classes) = $this->getInstancesList($ids);
        $movableInstances = $this->getInstancesToMove($classes, $instances, $statuses);

        $statuses = $this->move($destinationClass, $movableInstances, $statuses);

        return [
            'success' => true,
            'data' => $statuses
        ];
    }

    /**
     * Gets list of existing instances/classes
     *
     * @param array $ids list of ids asked to be moved
     * @return array
     */
    private function getInstancesList(array $ids)
    {
        $statuses = $instances = $classes = [];

        foreach ($ids as $key => $instance) {
            $instance = $this->getResource($instance);
            if ($instance->isClass()) {
                $instance = $this->getClass($instance);
                $classes[] = $instance;
            } elseif (!$instance->exists()) {
                $statuses[$instance->getUri()] = [
                    'success' => false,
                    'message' => sprintf('Instance "%s" does not exist', $instance->getUri()),
                ];
                break;
            }
            $instances[$key] = $instance;
        }

        return [
            $statuses,
            $instances,
            $classes
        ];
    }

    /**
     * Get movable instances from the list of instances
     *
     * @param array $classes
     * @param array $instances
     *
     * @return array
     */
    private function getInstancesToMove(array $classes = [], array $instances = [], array &$statuses = [])
    {
        $movableInstances = [];

        // Check if a class belong to class to move
        /** @var core_kernel_classes_Resource|core_kernel_classes_Class $instance */
        foreach ($instances as $instance) {
            $isValid = true;
            foreach ($classes as $class) {
                if ($instance instanceof core_kernel_classes_Class) {
                    //Disallow moving a class to $class. True only for classes which are already subclasses of $class
                    if ($class->getUri() != $instance->getUri() && $instance->isSubClassOf($class)) {
                        $statuses[$instance->getUri()] = [
                            'success' => false,
                            'message' => sprintf('Instance "%s" cannot be moved to class to move "%s"', $instance->getUri(), $class->getUri()),
                        ];
                        $isValid = false;
                        break;
                    }
                } else {
                    //Disallow moving instances to $class. True only for instances which already belongs to $class
                    if ($instance->isInstanceOf($class)) {
                        $statuses[$instance->getUri()] = [
                            'success' => false,
                            'message' => sprintf('Instance "%s" cannot be moved to class to move "%s"', $instance->getUri(), $class->getUri()),
                        ];
                        $isValid = false;
                        break;
                    }
                }
            }
            if ($isValid) {
                $movableInstances[$instance->getUri()] = $instance;
            }
        }

        return $movableInstances;
    }

    /**
     * Move movableInstances to the destinationClass
     *
     * @param core_kernel_classes_Class $destinationClass class to move to
     * @param array $movableInstances list of instances available to move
     * @param array $statuses list of statuses for instances asked to be moved
     *
     * @return array $statuses updated list of statuses
     */
    private function move(\core_kernel_classes_Class $destinationClass, array $movableInstances = [], array $statuses = [])
    {
        /** @var core_kernel_classes_Resource $movableInstance */
        foreach ($movableInstances as $movableInstance) {
            $statuses[$movableInstance->getUri()] = [
                'success' => $success = $this->getClassService()->changeClass($movableInstance, $destinationClass),
            ];
            if ($success === true) {
                $statuses[$movableInstance->getUri()]['message'] = sprintf(
                    'Instance "%s" has been successfully moved to "%s"',
                    $movableInstance->getUri(),
                    $destinationClass->getUri()
                );
            } else {
                $statuses[$movableInstance->getUri()]['message'] = sprintf('An error has occurred while persisting instance "%s"', $movableInstance->getUri());
            }
        }

        return $statuses;
    }

    /**
     * Return a formatted error message with code 406
     *
     * @param $message
     * @param int $httpStatusCode
     */
    protected function returnJsonError($message, $httpStatusCode = 406)
    {
        $response = [
            'success'  => false,
            'errorCode' => $httpStatusCode,
            'errorMessage' =>  $message
        ];
        $this->returnJson($response, 406);
    }

    protected function getExtraValidationRules(): array
    {
        return [];
    }

    protected function getExcludedProperties(): array
    {
        return [];
    }

    /**
     * @return ActionService
     */
    private function getActionService()
    {
        return $this->getServiceLocator()->get(ActionService::SERVICE_ID);
    }

    /**
     * Get the resource service
     * @return ResourceService
     */
    protected function getResourceService()
    {
        return $this->getServiceLocator()->get(ResourceService::SERVICE_ID);
    }

    /**
     * @return SignatureGenerator
     */
    private function getSignatureGenerator()
    {
        return $this->getServiceLocator()->get(SignatureGenerator::SERVICE_ID);
    }

    /**
     * @return string
     *
     * @throws InconsistencyConfigException
     */
    private function createFormSignature()
    {
        return $this->getSignatureGenerator()->generate(
            tao_helpers_Uri::encode($this->getRequestParameter('classUri'))
        );
    }

    /**
     * @param $destinationUri
     * @param $currentClassUri
     */
    private function validateDestinationClass($destinationUri, $currentClassUri)
    {
        $destinationClass = $this->getClass($destinationUri);
        if (empty($destinationUri) || $destinationUri === $currentClassUri || !$destinationClass->exists()) {
            throw new InvalidArgumentException('Wrong destination class uri');
        }
    }

    private function getActionAccessControl(): ActionAccessControl
    {
        return $this->getServiceLocator()->get(ActionAccessControl::SERVICE_ID);
    }
}
