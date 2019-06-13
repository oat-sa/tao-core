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
namespace oat\tao\model\routing;

use FastRoute\Dispatcher;
use oat\oatbox\service\ServiceManagerAwareTrait;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\MethodNotAllowedException;
use Slim\Router;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * A simple router, that maps a relative Url to
 * namespaced Controller class
 * 
 * @author Joel Bout, <joel@taotesting.com>
 */
class NamespaceRoute extends AbstractRoute implements ServiceLocatorAwareInterface, RouteWithPathVariables
{
    use ServiceManagerAwareTrait;

    const OPTION_NAMESPACE = 'namespace';

    /**
     * @var string[]
     */
    protected $pathVariables = [];
    
    public function resolve(ServerRequestInterface $request) {
        $relativeUrl = \tao_helpers_Request::getRelativeUrl($request->getRequestTarget());
        $parts = explode('/', $relativeUrl);
        $slash = strpos($relativeUrl, '/');
        if ($slash !== false && substr($relativeUrl, 0, $slash) == $this->getId()) {
	        $config = $this->getConfig();
	        $namespace = $config[self::OPTION_NAMESPACE];
	        $rest = substr($relativeUrl, $slash+1);
	        if (!empty($rest)) {
                $parts = explode('/', $rest, 3);
                $controller = rtrim($namespace, '\\').'\\'.$parts[0];

                /** @var RouteAnnotationService $routeAnnotationService */
                $routeAnnotationService = $this->getServiceLocator()->get(RouteAnnotationService::SERVICE_ID);
                $routeInfo = $routeAnnotationService->getRouteInfo($controller);
                if (!empty($routeInfo)) {
                    list($method, $this->pathVariables) =
                        $this->resolveUsingAnnotations($parts[0], $request, $routeInfo);
                    return $controller.'@'.$method;
                }

                $method = isset($parts[1]) ? $parts[1] : DEFAULT_ACTION_NAME;
                return $controller.'@'.$method;
            } elseif (defined('DEFAULT_MODULE_NAME') && defined('DEFAULT_ACTION_NAME')) {
                $controller = rtrim($namespace, '\\').'\\'.DEFAULT_MODULE_NAME;
                $method = DEFAULT_ACTION_NAME;
                return $controller.'@'.$method;
            }
        }
        return null;
    }

    /**
     * @param string $basePath
     * @param array $routeInfo
     * @return \Slim\Router
     */
    private function getSlimRouterFromAnnotations($basePath, array $routeInfo) {
        $slimRouter = new \Slim\Router();
        $slimRouter->setBasePath($basePath);

        foreach ($routeInfo as $routeAnnotation) {
            $path = rtrim($basePath, '/') . '/' . ltrim($routeAnnotation['path'], '/');
            $slimRouter->map([$routeAnnotation['method']], $path,
                function () use ($routeAnnotation) { return $routeAnnotation['target']; }
                );
        }

        return $slimRouter;
    }

    /**
     * @param string $partController
     * @param ServerRequestInterface $request
     * @param array $routeInfo
     * @return array|[string, array]
     * @throws RouterException
     * @throws \common_exception_MethodNotAllowed
     * @throws \common_exception_ResourceNotFound
     */
    private function resolveUsingAnnotations(
        $partController,
        ServerRequestInterface $request,
        array $routeInfo
    ) {
        $basePath = '/' . $this->getId() . '/' . $partController;
        $slimRouter = $this->getSlimRouterFromAnnotations($basePath, $routeInfo);
        $result = $slimRouter->dispatch($request);
        if (count($result) === 1 && $result[0] === Dispatcher::NOT_FOUND) {
            throw new \common_exception_ResourceNotFound('Not Found');
        }
        if (count($result) === 2 && $result[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            throw new \common_exception_MethodNotAllowed('Method Not Allowed', 0 , $result[1]);
        }
        if (count($result) === 3 && $result[0] === Dispatcher::FOUND) {
            $method = $slimRouter->getRoutes()[$result[1]]->getCallable()();
            $pathVariables = $result[2];
            return [$method, $pathVariables];
        }
        throw new RouterException('Unexpected error');
    }

    /**
     * @inheritdoc
     */
    public function getPathVariables()
    {
        return $this->pathVariables;
    }

    /**
     * Get controller namespace prefix
     * @return string
     */
    public static function getControllerPrefix()
    {
        return '';
    }
}
