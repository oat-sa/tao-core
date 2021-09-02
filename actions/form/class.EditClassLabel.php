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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

use oat\generis\model\OntologyRdfs;
use oat\tao\helpers\form\validators\ResourceSignatureValidator;
use oat\tao\model\security\SignatureValidator;

/**
 *
 * This form let's you edit the label of a class, only.
 *
 * @author Bertrand Chevrier, <bertrand@taotesting.com>
 */
class tao_actions_form_EditClassLabel extends \tao_helpers_form_FormContainer
{
    /**
     * @var core_kernel_classes_Class
     */
    protected $clazz;
    /**
     * @var string
     */
    private $signature;


    /**
     * @param core_kernel_classes_Class $clazz
     * @param array $classData
     * @param string $signature
     * @param array $options
     */
    public function __construct(\core_kernel_classes_Class $clazz, $classData, $signature, $options = [])
    {
        $this->clazz = $clazz;
        $this->signature = $signature;

        parent::__construct($classData, $options);
    }

    /**
     * Class instance being authored
     *
     * @return core_kernel_classes_Class
     */
    protected function getClassInstance()
    {
        return $this->clazz;
    }

    /**
     * Initialize the form
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        (isset($this->options['name'])) ? $name = $this->options['name'] : $name = '';
        if (empty($name)) {
            $name = 'form_' . (count(self::$forms) + 1);
        }
        unset($this->options['name']);

        $this->form = \tao_helpers_form_FormFactory::getForm($name, $this->options);


        $this->form->setActions(\tao_helpers_form_FormFactory::getCommonActions(), 'bottom');
    }

    /**
     * Initialize the form elements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initElements()
    {
        $clazz = $this->getClassInstance();

        $labelProp = new \core_kernel_classes_Property(OntologyRdfs::RDFS_LABEL);
        //map properties widgets to form elements
        $element = \tao_helpers_form_GenerisFormFactory::elementMap($labelProp);
        if (!is_null($element)) {
            $value = $clazz->getLabel();
            if (!is_null($value)) {
                $element->setValue($value);
            }
            //set label validator
            $element->addValidators([
                \tao_helpers_form_FormFactory::getValidator('NotEmpty'),
            ]);
            $namespace = substr($clazz->getUri(), 0, strpos($clazz->getUri(), '#'));
            if ($namespace != LOCAL_NAMESPACE) {
                $readonly = \tao_helpers_form_FormFactory::getElement($element->getName(), 'Readonly');
                $readonly->setDescription($element->getDescription());
                $readonly->setValue($element->getRawValue());
                $element = $readonly;
            }
            $element->addClass('global');
            $this->form->addElement($element);
        }

        //add an hidden elt for the class uri
        $classUriElt = \tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
        $classUriElt->setValue(\tao_helpers_Uri::encode($clazz->getUri()));
        $classUriElt->addClass('global');
        $this->form->addElement($classUriElt);

        $this->addSignature();
    }

    /**
     * @throws \common_Exception
     */
    protected function addSignature()
    {
        $signature = tao_helpers_form_FormFactory::getElement('signature', 'Hidden');

        $signature->setValue($this->signature);
        $signature->addValidator(
            new ResourceSignatureValidator(
                new SignatureValidator(),
                tao_helpers_Uri::encode($this->clazz->getUri())
            )
        );

        $this->form->addElement($signature, true);
    }
}
