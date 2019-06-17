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
use oat\tao\model\routing\AnnotationReader\route;
use ReflectionClass;
use ReflectionMethod;

class AnnotationReaderService extends ConfigurableService
{
    const SERVICE_ID = 'tao/AnnotationReaderService';

    const OPTION_DISABLE_CACHE = 'disable_cache';

    const KEY_PREFIX = 'routeAnnotation_';
    const PROP_RIGHTS = 'required_rights';
    const PROP_SECURITY = 'security';
    const PROP_ROUTE = 'route';

    const METHODS_PUBLIC = '*PUBLIC';
    const METHOD_KEY_PREFIX = 'method:';

    /**
     * @var \common_cache_Cache
     */
    private $cacheService;

    /**
     * @param $methodAnnotationKey
     * @return string|null
     */
    public static function getMethodNameFromKey($methodAnnotationKey) {
        if (strpos($methodAnnotationKey, self::METHOD_KEY_PREFIX) !== 0) {
            return null;
        }
        return substr($methodAnnotationKey, strlen(self::METHOD_KEY_PREFIX));
    }

    /**
     * @param string $className
     * @param string $methodName get annotations for specified method or for whole class (if empty)
     * @param string|null $annotationClass parse only specified annotation
     * @return array|mixed
     * @throws \common_cache_NotFoundException
     */
    public function getAnnotations($className, $methodName, $annotationClass = null)
    {
        $annotationKey = $this->getCacheKey($className, $methodName, $annotationClass);
        $cacheDisabled = $this->getOption(self::OPTION_DISABLE_CACHE);
        if (!$cacheDisabled && $this->getCacheService()->has($annotationKey)) {
            $annotations = json_decode($this->getCacheService()->get($annotationKey), true);
        } else {
            $annotations = $this->readAnnotations($className, $methodName, $annotationClass);
            if (!$cacheDisabled) {
                $this->getCacheService()->put(json_encode($annotations), $annotationKey);
            }
        }
        return $annotations;
    }

    /**
     * @param string $className
     * @param $methodName
     * @param null $annotationClass
     * @return bool if key existed
     */
    public function clearCache($className, $methodName, $annotationClass = null) {
        $annotationKey = $this->getCacheKey($className, $methodName, $annotationClass);
        if ($this->getCacheService()->has($annotationKey)) {
            $this->getCacheService()->remove($annotationKey);
            return true;
        }
        return false;
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param string|null $annotationClass
     * @return string
     */
    private function getCacheKey($className, $methodName, $annotationClass = null) {
        $annotationKey = self::KEY_PREFIX . $className . $methodName;
        if ($annotationClass) {
            $annotationKey .= '@' . $annotationClass;
        }
        return $annotationKey;
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param string|null $annotationClass
     * @return array
     */
    private function readAnnotations($className, $methodName, $annotationClass)
    {
        $rules = [
            self::PROP_RIGHTS => [],
            self::PROP_SECURITY => [],
            self::PROP_ROUTE => []
        ];
        try {
            // we need to define class
            // we need to change autoloader file without this, on each environment
            new requiredRights();
            new security();
            new route();
            $annotationReader = new AnnotationReader();

            $publicMethodsAnnotations = [];
            $annotations = [];
            if ($methodName === self::METHODS_PUBLIC) {
                $reflectionClass = new ReflectionClass($className);
                foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                    $methodAnnotations = $this->filterAnnotations(
                        $annotationReader->getMethodAnnotations($reflectionMethod),
                        $annotationClass
                    );
                    if (!empty($methodAnnotations)) {
                        $publicMethodsAnnotations[self::METHOD_KEY_PREFIX . $reflectionMethod->getName()] = $methodAnnotations;
                    }
                }
            } else if ($methodName) {
                $reflectionMethod = new ReflectionMethod($className, $methodName);
                $annotations = $this->filterAnnotations(
                    $annotationReader->getMethodAnnotations($reflectionMethod),
                    $annotationClass
                );
            } else {
                $reflectionClass = new ReflectionClass($className);
                $annotations = $this->filterAnnotations(
                    $annotationReader->getClassAnnotations($reflectionClass),
                    $annotationClass
                );
            }
            foreach ($annotations as $annotation) {
                list($ruleKey, $ruleValue) = $this->annotationToRule($annotation);
                $rules[$ruleKey][] = $ruleValue;
            }
            foreach ($publicMethodsAnnotations as $methodKey => $annotations) {
                foreach ($annotations as $annotation) {
                    list($ruleKey, $ruleValue) = $this->annotationToRule($annotation);
                    $rules[$ruleKey][$methodKey][] = $ruleValue;
                }
            }
        } catch (\Exception $e) {
            $this->logNotice('Undefined annotation: ' . $e->getMessage());
        }
        return $rules;
    }

    /**
     * @param object $annotation
     * @return array [string, mixed] , [null, null] in case of unsupported annotation
     */
    private function annotationToRule($annotation) {
        switch (get_class($annotation)) {
            case requiredRights::class :
                return [self::PROP_RIGHTS, (array) $annotation];
            case security::class :
                return [self::PROP_SECURITY, $annotation->value];
            case route::class :
                return [self::PROP_ROUTE, (array) $annotation];
        }
        return [null, null];
    }

    /**
     * @param object[] $annotations
     * @param string|null $annotationClass
     * @return object[]
     */
    private function filterAnnotations(array $annotations, $annotationClass = null) {
        if ($annotationClass === null) {
            return $annotations;
        }
        return array_filter($annotations, static function ($annotation) use ($annotationClass) {
            return $annotation instanceof $annotationClass;
        });
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
