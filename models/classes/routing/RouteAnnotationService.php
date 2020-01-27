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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\tao\model\routing;

use oat\oatbox\service\ConfigurableService;

class RouteAnnotationService extends ConfigurableService
{
    const SERVICE_ID = 'tao/routeAnnotation';

    const SECURITY_HIDE = 'hide';
    const SECURITY_ALLOW = 'allow';
    const SECURITY_DENY = 'deny';

    /**
     * @param string $className
     * @param string $methodName
     * @return bool
     */
    public function isHidden($className, $methodName)
    {
        try {
            $annotations = $this->getAnnotations($className, $methodName);
            $hidden = array_key_exists(AnnotationReaderService::PROP_SECURITY, $annotations)
                && in_array(self::SECURITY_HIDE, $annotations[AnnotationReaderService::PROP_SECURITY], true);
        } catch (\Exception $e) {
            $hidden = false; // if class or method not found
        }

        return $hidden;
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return bool
     */
    public function hasAccess($className, $methodName = '')
    {
        $access = true;
        try {
            $annotations = $this->getAnnotations($className, $methodName);
            if (
                array_key_exists(AnnotationReaderService::PROP_SECURITY, $annotations)
                && is_array($annotations[AnnotationReaderService::PROP_SECURITY])
            ) {
                foreach ($annotations[AnnotationReaderService::PROP_SECURITY] as $rule) {
                    switch ($rule) {
                        case self::SECURITY_HIDE:
                        case self::SECURITY_DENY:
                            $access = false;
                            break;
                        case self::SECURITY_ALLOW:
                            // do not change state (it will be allowed by default but closed by hidden & deny)
                            break;
                        // any unsupported actions return false
                        default:
                            $access = false;
                    }
                }
            }
        } catch (\Exception $e) {
            $access = false; // if class or method not found
        }

        return $access;
    }

    public function getRights($className, $methodName = '')
    {
        $res = [];
        try {
            $annotations = $this->getAnnotations($className, $methodName);
            if (array_key_exists(AnnotationReaderService::PROP_RIGHTS, $annotations)) {
                foreach ($annotations[AnnotationReaderService::PROP_RIGHTS] as $rule) {
                    $res[$rule['key']] = $rule['permission'];
                }
            }
        } catch (\Exception $e) {
        }
        return $res;
    }

    private function getAnnotations($className, $methodName)
    {
        return $this->getServiceLocator()
            ->get(AnnotationReaderService::SERVICE_ID)
            ->getAnnotations($className, $methodName);
    }
}
