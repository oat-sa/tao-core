<?php
declare(strict_types=1);

namespace oat	ao\scripts\install;

use oat\oatbox\service\ServiceManager;
use oat	ao\models\TaskOrchestrator\TaskOrchestratorEmailService;
use oat	ao\scripts\InstallAction;

/**
 * Temporary script to test Task Orchestrator email integration.
 */
class TestTaskOrchestratorEmailAction extends InstallAction
{
    public function run(): int
    {
        $this->log('INFO', 'Attempting to send a test email via Task Orchestrator...');

        /** @var ServiceManager $serviceManager */
        $serviceManager = $this->getServiceManager();

        try {
            /** @var TaskOrchestratorEmailService $emailService */
            $emailService = $serviceManager->get(TaskOrchestratorEmailService::class);

            // --- Configure test email parameters ---
            // These should ideally come from command line arguments or a test config
            // For a temporary test script, hardcoding can be acceptable.
            $testTemplateId = 'mfa-code-email'; // Example templateId, ensure it exists in your templates worker config
            $testRecipientUserLogin = 'bartlomiej@taotesting.com'; // Replace with a valid portal-user login
            $testTemplateData = [
                'name' => 'Test User',
                'mfaCode' => '123456',
                'validityTime' => 3600,
                'locale' => 'en-US',
            ];
            // -------------------------------------

            $jobId = $emailService->sendEmail(
                $testTemplateId,
                $testRecipientUserLogin,
                $testTemplateData
            );

            $this->log('INFO', sprintf('Successfully sent test email job to Task Orchestrator. Job ID: %s', $jobId));
            $this->log('INFO', 'Please check Task Orchestrator logs and recipient's inbox for verification.');

            return 0; // Success
        } catch (\Throwable $e) {
            $this->log('ERROR', sprintf('Failed to send test email via Task Orchestrator: %s', $e->getMessage()));
            $this->log('ERROR', 'Ensure all environment variables (TASK_ORCHESTRATOR_API_URL, etc.) are set, and Task Orchestrator services are running.');
            return 1; // Failure
        }
    }
}
