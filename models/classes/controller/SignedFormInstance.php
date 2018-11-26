<?php

namespace oat\tao\model\controller;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\tao\helpers\form\validators\ResourceSignatureValidator;
use tao_actions_form_Instance;
use tao_helpers_form_FormFactory;
use tao_helpers_Uri;

abstract class SignedFormInstance extends tao_actions_form_Instance
{
    const SIGNATURE_ELEMENT_NAME = 'signature';

    /**
     * @var string
     */
    private $signature;

    /**
     * @param core_kernel_classes_Class $clazz
     * @param core_kernel_classes_Resource|null $instance
     * @param string $signature
     * @param array $options
     */
    public function __construct(
        core_kernel_classes_Class $clazz,
        $signature,
        core_kernel_classes_Resource $instance = null,
        array $options = []
    ) {
        $this->signature = $signature;

        parent::__construct($clazz, $instance, $options);
    }

    /**
     * @return mixed|void
     * @throws \common_Exception
     */
    protected function initElements()
    {
        parent::initElements();

        $this->addSignature();
    }

    /**
     * @throws \common_Exception
     */
    protected function addSignature()
    {
        $signature = tao_helpers_form_FormFactory::getElement(self::SIGNATURE_ELEMENT_NAME, 'Hidden');

        $signature->setValue($this->signature);
        $signature->addValidator(
            new ResourceSignatureValidator(
                tao_helpers_Uri::encode($this->instance->getUri()),
                tao_helpers_Uri::encode($this->clazz->getUri())
            )
        );

        $this->form->addElement($signature, true);
    }
}
