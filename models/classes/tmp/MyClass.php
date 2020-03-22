<?php

declare(strict_types=1);

namespace oat\tao\model\tmp;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\session\SessionService;

class MyClass extends ConfigurableService implements MyInterface
{
    public function run(): string
    {
        return sprintf('HELLO %s', $this->getSessionService()->getCurrentUser()->getIdentifier());
    }

    private function getSessionService(): SessionService
    {
        return $this->getServiceLocator()->get(SessionService::SERVICE_ID);
    }
}
