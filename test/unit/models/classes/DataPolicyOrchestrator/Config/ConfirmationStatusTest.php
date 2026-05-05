<?php

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\DataPolicyOrchestrator\Config;

use oat\tao\model\DataPolicyOrchestrator\Config\ConfirmationStatus;
use PHPUnit\Framework\TestCase;

class ConfirmationStatusTest extends TestCase
{
    public function testByErrorsReturnsRemovedWhenErrorsAreEmpty(): void
    {
        $this->assertSame('removed', ConfirmationStatus::byErrors([]));
    }

    public function testByErrorsReturnsFailedWhenErrorsArePresent(): void
    {
        $this->assertSame('failed', ConfirmationStatus::byErrors(['error']));
    }
}
