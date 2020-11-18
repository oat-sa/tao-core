<?php

declare(strict_types=1);

namespace oat\tao\model\preview;

use oat\oatbox\AbstractRegistry;
use oat\tao\model\modules\DynamicModule;

/**
 * Interface PreviewerRegistryServiceInterface
 *
 * @package oat\tao\model\preview
 *
 * @author Andrew Shapiro <shpr.andrew@gmail.com>
 */
interface PreviewerRegistryServiceInterface
{
    /**
     * @return AbstractRegistry
     */
    public function getRegistry(): AbstractRegistry;

    /**
     * @param AbstractRegistry $registry
     */
    public function setRegistry(AbstractRegistry $registry): void;

    /**
     * @return array
     */
    public function getAdapters(): array;

    /**
     * @param DynamicModule $module
     *
     * @return bool
     */
    public function registerAdapter(DynamicModule $module): bool;

    /**
     * @param string $moduleId
     *
     * @return bool
     */
    public function unregisterAdapter(string $moduleId): bool;

    /**
     * @return array
     */
    public function getPlugins(): array;

    /**
     * @param DynamicModule $module
     *
     * @return bool
     */
    public function registerPlugin(DynamicModule $module): bool;

    /**
     * @param string $module
     *
     * @return bool
     */
    public function unregisterPlugin(string $module): bool;
}
