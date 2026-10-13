<?php

declare(strict_types=1);

namespace oat\tao\model\lockDownBrowser\Service;

final class LockDownBrowserDetectionService
{
    public const SERVICE_ID = 'tao/LockDownBrowserDetection';

    private LockDownBrowserDetectorInterface $detector;

    public function __construct(LockDownBrowserDetectorInterface $detector)
    {
        $this->detector = $detector;
    }

    /**
     * @param mixed|null $clientKioskApiOk Passed from Runner/init when available
     */
    public function detect($clientKioskApiOk = null): string
    {
        return $this->detector->detect($clientKioskApiOk);
    }
}
