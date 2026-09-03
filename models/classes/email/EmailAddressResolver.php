<?php
declare(strict_types=1);

namespace oat\tao\model\email;

use oat\oatbox\user\User;
use oat\tao\helpers\UserHelper;

class EmailAddressResolver implements EmailAddressResolverInterface
{
    /**
     * Resolves the email address for a given user using the UserHelper.
     */
    public function resolve(User $user): ?string
    {
        $email = UserHelper::getUserMail($user);
        return is_string($email) && $email !== '' ? $email : null;
    }
}

