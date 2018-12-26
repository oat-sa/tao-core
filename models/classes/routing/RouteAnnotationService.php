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
     * @param $className
     * @param string $methodName
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     * @throws \ResolverException
     */
    public function validate($className, $methodName = '')
    {
        // we need to define class, without we need to change autoloader file on each environment
        new RouteAnnotation();
        $reflectionMethod = new ReflectionMethod($className, $methodName);
        $annotationReader = new AnnotationReader();
        $annotation = $annotationReader->getMethodAnnotation($reflectionMethod, RouteAnnotation::class);

        if ($annotation instanceof RouteAnnotation && $annotation->getAction() == 'NotFound') {
            throw new \ResolverException('Blocked by the method annotation');
        }
    }
}
