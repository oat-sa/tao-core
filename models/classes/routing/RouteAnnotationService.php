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

    const KEY_PREFIX = 'routeAnnotation_';

    /**
     * @var \common_cache_Cache
     */
    private $cacheService;
    private function getCacheService()
    {
        if (!$this->cacheService) {
            if ($this->hasOption('cacheService') && $this->getOption('cacheService') instanceof \common_cache_Cache) {
                $this->cacheService = $this->getOption('cacheService');
            } else {
                $this->cacheService = $this->getServiceLocator()->get(\common_cache_Cache::SERVICE_ID);
            }
        }

        return $this->cacheService;
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return bool
     */
    public function hasNotFoundAction($className, $methodName)
    {
        try {
            $annotation = $this->getAnnotation($className, $methodName);
            $hasAction = $annotation instanceof RouteAnnotation && $annotation->getAction() === 'NotFound';
        } catch (\Exception $e) {
            $hasAction = false; // if class or method not found
        }

        return $hasAction;
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return bool
     */
    public function hasAccess($className, $methodName)
    {
        $access = true;
        try {
            $annotation = $this->getAnnotation($className, $methodName);
            if ($annotation instanceof RouteAnnotation) {
                switch ($annotation->getAction()) {
                    case 'NotFound':
                        $access = false;
                        break;
                    case 'allow':
                        $access = $this->hasRights($annotation);
                        break;
                    // any unsupported actions return false
                    default: $access = false;
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
        $annotationKey = self::KEY_PREFIX . $className . $methodName;
        try {
            $annotation = unserialize($this->getCacheService()->get($annotationKey));
        } catch (\common_cache_NotFoundException $e) {
            $annotation = $this->readAnnotation($className, $methodName);
            $this->getCacheService()->put(serialize($annotation), $annotationKey);
        }

        return $annotation;
    }
    
    private function readAnnotation($className, $methodName)
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
            $annotation = new RouteAnnotation();
            $this->logNotice('Undefined annotation: ' . $e->getMessage());
        }
        return $annotation;
    }
}
