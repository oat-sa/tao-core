<?php

declare(strict_types=1);

namespace oat\tao\model\resources\Service;

use core_kernel_classes_Class;

interface ClassDeleterInterface
{
    public function delete(core_kernel_classes_Class $class, core_kernel_classes_Class $rootClass): void;

    public function isDeleted(core_kernel_classes_Class $class): bool;
}
