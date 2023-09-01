<?php

use oat\tao\model\service\ApplicationService;
use oat\tao\model\http\HttpJsonResponseTrait;

class tao_actions_Health extends tao_actions_CommonModule
{
    use HttpJsonResponseTrait;

    /**
     * Simple endpoint for health checking the TAO instance.
     *
     * No need authentication.
     * The client only needs a 200 response.
     */
    public function index()
    {
        try {
            /** @var ApplicationService $applicationService */
            $applicationService = $this->getServiceLocator()->get(ApplicationService::SERVICE_ID);
            if ($applicationService->isInstallationFinished()) {
                $this->setSuccessJsonResponse([
                    'success' => true
                ]);
            } else {
                throw new common_exception_Error('Installation is not finished');
            }
        } catch (Throwable $exception) {
            $this->logError(sprintf('Error during health check: %s, ', $exception->getMessage()));
            $this->setErrorJsonResponse($exception->getMessage(), $exception->getCode());
        }
    }
}
