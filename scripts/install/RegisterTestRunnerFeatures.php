<?php
/**
 * ***********************************************************************
 * INVALSI CONFIDENTIAL
 * __________________
 *
 *  All Rights Reserved.
 *
 *  NOTICE:  All information contained herein is, and remains
 *  the property of INVALSI and its suppliers, if any.  The
 *  intellectual and technical concepts contained herein are
 *  proprietary to INVALSI and its suppliers, and are protected
 *  by trade secret or copyright law.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from INVALSI.
 *  ************************************************************************
 */

namespace oat\taoInvalsi\scripts\install;

use oat\taoInvalsi\model\runner\features\ProgressBar;
use oat\taoTests\models\runner\features\TestRunnerFeatureService;

class RegisterTestRunnerFeatures extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $serviceManager = $this->getServiceManager();

        $testRunnerFeatureService = $serviceManager->get(TestRunnerFeatureService::SERVICE_ID);
        $features = $testRunnerFeatureService->getAll();

        if (!isset($features['progressBar'])) {
            $testRunnerFeatureService->register(new ProgressBar());
            $serviceManager->register(TestRunnerFeatureService::SERVICE_ID, $testRunnerFeatureService);
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Test runner features registered');
    }
}