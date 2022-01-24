<?php

declare(strict_types = 1);

namespace oat\tao\model\config;

interface BackupConfigServiceInterface
{
    public function makeCopy(): void;
}
