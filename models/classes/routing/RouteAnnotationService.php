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


use Doctrine\Common\Annotations\AnnotationReader;
use oat\oatbox\service\ConfigurableService;
use ReflectionMethod;

class RouteAnnotationService extends ConfigurableService
{
    const SERVICE_ID = 'tao/routeAnnotation';

    /**
     * @param RouteAnnotation $annotation
     * @return bool
     */
    public function isNotFound(RouteAnnotation $annotation = null)
    {
        try {
            $notFound = $annotation instanceof RouteAnnotation && $annotation->getAction() === 'NotFound';
        } catch (\Exception $e) {
            $notFound = false; // if class or method not found
        }

        return $notFound;
    }

    /**
     * @param RouteAnnotation $annotation
     * @return bool
     */
    public function hasAccess(RouteAnnotation $annotation = null)
    {
        $access = true;
        try {
            if ($annotation instanceof RouteAnnotation) {
                switch ($annotation->getAction()) {
                    case 'NotFound':
                        $access = false;
                        break;
                    case 'allow':
                        $access = $this->hasRights($annotation);
                        break;
                }
            }
        }  catch (\Exception $e) {
            $access = false; // if class or method not found
        }

        return $access;
    }

    private function hasRights(RouteAnnotation $annotation)
    {
        $requiredRights = $annotation->getRequiredRights();
        // todo implement it
        return false;
    }

    /**
     * @param $className
     * @param $methodName
     * @return RouteAnnotation
     */
    public function getAnnotation($className, $methodName)
    {
        $annotation = null;
        try {
            // we need to define class
            // we need to change autoloader file without this, on each environment
            new RouteAnnotation();
            $reflectionMethod = new ReflectionMethod($className, $methodName);
            $annotationReader = new AnnotationReader();
            $annotation = $annotationReader->getMethodAnnotation($reflectionMethod, RouteAnnotation::class);
        } catch (\Exception $e) {
            $this->logNotice('Undefined annotation: ' . $e->getMessage());
        }
        return $annotation;
    }
}
