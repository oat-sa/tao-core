<?php

declare(strict_types=1);

class tao_actions_Health extends tao_actions_CommonModule
{
    /**
     * Simple endpoint for health checking the TAO instance.
     *
     * No need authentication.
     * The client only needs a 200 response.
     */
    public function index(): void
    {
        return;
    }
}
