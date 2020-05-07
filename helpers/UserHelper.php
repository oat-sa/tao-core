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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\helpers;

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\user\User;
use core_kernel_classes_Resource;
use core_kernel_users_GenerisUser;
use Jig\Utils\StringUtils;
use oat\oatbox\user\UserService;

/**
 * Utility class to render a User
 *
 * @author Joel Bout <joel@taotesting.com>
 * @package tao
 */
class UserHelper
{
    public static function renderHtmlUser($userId)
    {
        // assume generis user
        $user = self::getUser($userId);
        $labels = $user->getPropertyValues(OntologyRdfs::RDFS_LABEL);
        $mails = $user->getPropertyValues(GenerisRdf::PROPERTY_USER_MAIL);
        $label = !empty($labels) ? (string)reset($labels) : '(' . $userId . ')';
        $label = StringUtils::wrapLongWords($label);
        $mail = !empty($mails) ? (string)reset($mails) : '';
        return !empty($mail)
            ? '<a href="mailto:' . $mail . '">' . $label . '</a>'
            : $label;
    }

    /**
     * Gets a user from a URI
     *
     * @todo Make a stronger helper which take care of provider (LDAP, OAUTH, etc.)
     *
     * @param string $userId
     * @return User
     */
    public static function getUser($userId)
    {
        $userService = ServiceManager::getServiceManager()->get(UserService::SERVICE_ID);
        return $userService->getUser($userId);
    }

    /**
     * Gets the value of a string property from a user
     * @param User $user
     * @param string $property
     * @return string
     */
    public static function getUserStringProp(User $user, $property)
    {
        $value = $user->getPropertyValues($property);
        return empty($value) ? '' : current($value);
    }

    /**
     * Gets the user's label
     * @param User $user
     * @return string
     */
    public static function getUserLabel(User $user)
    {
        return self::getUserStringProp($user, OntologyRdfs::RDFS_LABEL);
    }

    /**
     * Gets the user's login
     * @param User $user
     * @return string
     */
    public static function getUserLogin(User $user)
    {
        return self::getUserStringProp($user, GenerisRdf::PROPERTY_USER_LOGIN);
    }

    /**
     * Gets the user's email
     * @param User $user
     * @return string
     */
    public static function getUserMail(User $user)
    {
        return self::getUserStringProp($user, GenerisRdf::PROPERTY_USER_MAIL);
    }

    /**
     * Gets the user's first name
     * @param User $user
     * @param bool $defaultToLabel
     * @return string
     */
    public static function getUserFirstName(User $user, $defaultToLabel = false)
    {
        $firstName = self::getUserStringProp($user, GenerisRdf::PROPERTY_USER_FIRSTNAME);

        if (empty($firstName) && $defaultToLabel) {
            $firstName = self::getUserLabel($user);
        }
        
        return $firstName;
    }
    
    /**
     * Gets the user's last name
     * @param User $user
     * @param bool $defaultToLabel
     * @return string
     */
    public static function getUserLastName(User $user, $defaultToLabel = false)
    {
        $lastName = self::getUserStringProp($user, GenerisRdf::PROPERTY_USER_LASTNAME);

        if (empty($lastName) && $defaultToLabel) {
            $lastName = self::getUserLabel($user);
        }
        
        return $lastName;
    }

    /**
     * Gets the full user name
     * @param User $user
     * @param bool $defaultToLabel
     * @return string
     */
    public static function getUserName(User $user, $defaultToLabel = false)
    {
        $firstName = self::getUserStringProp($user, GenerisRdf::PROPERTY_USER_FIRSTNAME);
        $lastName = self::getUserStringProp($user, GenerisRdf::PROPERTY_USER_LASTNAME);
        
        $userName = trim($firstName . ' ' . $lastName);
        
        if (empty($userName) && $defaultToLabel) {
            $userName = self::getUserLabel($user);
        }

        return $userName;
    }
}
