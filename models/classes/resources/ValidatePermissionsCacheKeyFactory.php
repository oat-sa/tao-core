<?php

declare(strict_types=1);

namespace oat\tao\model\resources;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;

class ValidatePermissionsCacheKeyFactory extends ConfigurableService
{
    public function create(string $resource, User $user): string
    {
        return sprintf('validperm:%s:%s', urlencode($user->getIdentifier()), urlencode($resource));
    }
}
