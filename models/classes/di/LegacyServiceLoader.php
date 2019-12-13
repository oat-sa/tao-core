<?php


namespace oat\tao\model\di;


use Closure;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Gateway;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Throwable;

class LegacyServiceLoader extends FileLoader
{

    /**
     * @inheritDoc
     */
    public function load($resource, string $type = null)
    {
        // the container and loader variables are exposed to the included file below
        $container = $this->container;
        $loader = $this;

        $load = Closure::bind(
            function ($path) use ($container, $loader, $resource, $type) {
                return include $path;
            },
            $this,
            ProtectedPhpFileLoader::class
        );
        foreach ($this->glob($resource, false, $globResource) as $path => $info) {
            // @TODO Should be be based at least on AST instead of code evaluation \ reflection
//            $ast = \ast\parse_file($path);
            try {
                $callback = $load($path);
                $class = get_class($callback);
                $serviceName = $class;

                $sampleService = new ReflectionClass($class);
                $interfaces = $sampleService->getInterfaces();

                $bInterface = array_filter(
                    $interfaces,
                    function (ReflectionClass $int) {
                        return array_key_exists('SERVICE_ID', $int->getConstants());
                    }
                );
                if ($bInterface) {
                    $i = array_pop($bInterface);
                    $serviceName = $i->getName();
                }

                $alias = end(explode('/', pathinfo($info, PATHINFO_DIRNAME))) . '/' . $info->getBasename('.conf.php');
                if ($callback instanceof ConfigurableService) {
                    ///&& defined($callback::SERVICE_ID)) {
//                    $alias = $callback::SERVICE_ID;
                    $alias = end(explode('/', pathinfo($info, PATHINFO_DIRNAME))) . '/' . $info->getBasename(
                            '.conf.php'
                        );
                }

                $definition = new Definition($serviceName);
                $definition->setAutowired(true)
                    ->setPublic(true)
                    ->setFactory(new Reference(Gateway::class))
                    ->setArguments([$alias])
                    ;
                $container->setDefinition($serviceName, $definition);

                $container->setAlias($alias, $serviceName)
                    ->setPublic(true);
                if ($class !== $serviceName) {
                    $container->setAlias($class, $serviceName)
                        ->setPublic(true);
                }
            } catch (Throwable $exception) {
                var_dump($exception->getMessage());
            } finally {
                $this->instanceof = [];
                $this->registerAliasesForSinglyImplementedInterfaces();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, string $type = null)
    {
        return stripos($resource, '*.conf.php') !== 0;
    }
}

/**
 * @internal
 */
final class ProtectedPhpFileLoader extends PhpFileLoader
{
}
