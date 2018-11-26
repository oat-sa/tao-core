<?php

namespace oat\tao\model\controller;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\security\SignatureGenerator;

trait SignatureCheckTrait
{
    abstract public function getRequestParameter($name);

    abstract public function hasRequestParameter($name);

    protected function getGeneratedSignatureFromRequest()
    {
        if ($this->hasRequestParameter('uri')) {
            $uri = $this->getRequestParameter('uri');
        } elseif ($this->hasRequestParameter('classUri')) {
            $uri = $this->getRequestParameter('classUri');
        } else {
            throw new \Exception('Wrong uri/classUri parameter is absent');
        }

        /** @var SignatureGenerator $signatureGenerator */
        $signatureGenerator = ServiceManager::getServiceManager()->get(SignatureGenerator::class);

        return $signatureGenerator->generate($uri);
    }

    protected function checkSignature()
    {
        $signature = $this->getRequestParameter('signature');

        if (empty($signature)) {
            throw new \Exception('Empty signature');
        }

        $generatedSignature = $this->getGeneratedSignatureFromRequest();

        if ($signature !== $generatedSignature) {
            throw new \Exception('Invalid signature');
        }
    }
}
