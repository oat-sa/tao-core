<?php
declare(strict_types=1);

namespace oat\tao\model\email;

use oat\oatbox\user\User;

interface EmailAddressResolverInterface
{
    public function resolve(User $user): ?string;
}
