<?php
declare(strict_types=1);

namespace oat\tao\models\classes\email;

class EmailSendResult
{
    private bool $initiated;
    private string $reasonCode;

    public function __construct(bool $initiated, string $reasonCode)
    {
        $this->initiated = $initiated;
        $this->reasonCode = $reasonCode;
    }

    public function isInitiated(): bool
    {
        return $this->initiated;
    }

    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }
}
