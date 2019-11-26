<?php

declare(strict_types=1);

namespace oat\tao\model\action;

/**
 * Interface for CommonModule
 *
 * @author Martijn Swinkels <martijn@taotesting.com>
 */
interface CommonModuleInterface
{
    /**
     * Initialization for the controller.
     */
    public function initialize();
}
