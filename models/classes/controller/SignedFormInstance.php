<?php

namespace oat\tao\model\controller;

use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\form\validators\ResourceSignatureValidator;
use oat\tao\model\security\SignatureGenerator;
use oat\tao\model\security\SignatureValidator;
use tao_actions_form_Instance;
use tao_helpers_form_FormFactory;

class SignedFormInstance extends tao_actions_form_Instance
{
    const SIGNATURE_ELEMENT_NAME = 'signature';

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

        $signature->setValue($this->getSignature());
        $signature->addValidator(
            new ResourceSignatureValidator(
                new SignatureValidator(),
                $this->getDataToSign()
            )
        );

        $this->form->addElement($signature, true);
    }

    /**
     * @return string
     * @throws \oat\tao\model\metadata\exception\InconsistencyConfigException
     */
    protected function getSignature()
    {
        /** @var SignatureGenerator $signatureGenerator */
        $signatureGenerator = ServiceManager::getServiceManager()->get(SignatureGenerator::class);

        return $signatureGenerator->generate($this->getDataToSign());
    }

    protected function getDataToSign()
    {
        return $this->instance->getUri();
    }
}
