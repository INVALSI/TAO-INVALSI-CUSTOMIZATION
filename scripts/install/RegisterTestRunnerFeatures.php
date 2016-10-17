<?php
namespace oat\taoInvalsi\scripts\install;

use oat\taoInvalsi\model\runner\plugins\Accessibility;
use oat\taoInvalsi\model\runner\plugins\ProgressBar;
use oat\taoTests\models\runner\plugins\TestRunnerFeatureService;

class RegisterTestRunnerFeatures extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $serviceManager = $this->getServiceManager();

        $testRunnerFeatureService = $serviceManager->get(TestRunnerFeatureService::SERVICE_ID);
        $testRunnerFeatureService->register(new ProgressBar());
        $testRunnerFeatureService->register(new Accessibility());

        $serviceManager->register(TestRunnerFeatureService::SERVICE_ID, $testRunnerFeatureService);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Test runner features registered');
    }
}