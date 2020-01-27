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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\tao\model\routing;

use Doctrine\Common\Annotations\AnnotationReader;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\routing\AnnotationReader\requiredRights;
use oat\tao\model\routing\AnnotationReader\security;
use ReflectionClass;
use ReflectionMethod;

class AnnotationReaderService extends ConfigurableService
{
    const SERVICE_ID = 'tao/AnnotationReaderService';

    const KEY_PREFIX = 'routeAnnotation_';
    const PROP_RIGHTS = 'required_rights';
    const PROP_SECURITY = 'security';

    /**
     * @var \common_cache_Cache
     */
    private $cacheService;

    /**
     * @param $className
     * @param $methodName
     * @return array|mixed
     * @throws \common_cache_NotFoundException
     */
    public function getAnnotations($className, $methodName)
    {
        $annotationKey = self::KEY_PREFIX . $className . $methodName;
        if ($this->getCacheService()->has($annotationKey)) {
            $annotation = json_decode($this->getCacheService()->get($annotationKey), true);
        } else {
            $annotation = $this->readAnnotations($className, $methodName);
            $this->getCacheService()->put(json_encode($annotation), $annotationKey);
        }
        return $annotation;
    }

    private function readAnnotations($className, $methodName)
    {
        $rules = [
            self::PROP_RIGHTS => [],
            self::PROP_SECURITY => [],
        ];
        try {
            // we need to define class
            // we need to change autoloader file without this, on each environment
            new requiredRights();
            new security();
            $annotationReader = new AnnotationReader();

            if ($methodName) {
                $reflectionMethod = new ReflectionMethod($className, $methodName);
                $annotations = $annotationReader->getMethodAnnotations($reflectionMethod);
            } else {
                $reflectionClass = new ReflectionClass($className);
                $annotations = $annotationReader->getClassAnnotations($reflectionClass);
            }
            foreach ($annotations as $annotation) {
                switch (get_class($annotation)) {
                    case requiredRights::class:
                        $rules[self::PROP_RIGHTS][] = (array) $annotation;
                        break;
                    case security::class:
                        $rules[self::PROP_SECURITY][] = $annotation->value;
                        break;
                }
            }
        } catch (\Exception $e) {
            $this->logNotice('Undefined annotation: ' . $e->getMessage());
        }
        return $rules;
    }

    /**
     * @return \common_cache_Cache
     */
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
}
