<?php

namespace oat\tao\useCases;

use oat\tao\model\Language\Repository\LanguageRepository;
use tao_actions_RdfController;

class LanguageController extends tao_actions_RdfController
{
    public function getUsers(): void
    {
        $this->returnJson([
            'locales' => $this->getLanguageService()->findAvailableLanguagesByUsage()->jsonSerialize()
        ]);
    }

    private function getLanguageService(): LanguageRepository
    {
        return $this->getServiceManager()->getContainer()->get(LanguageRepository::class);
    }

    protected function getRootClass()
    {
        return $this->getClassService()->getRootClass();
    }
}
