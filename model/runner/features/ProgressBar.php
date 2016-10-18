<?php

namespace oat\taoInvalsi\model\runner\features;

use oat\oatbox\service\ServiceManager;
use oat\taoTests\models\runner\features\TestRunnerFeature;
use oat\taoTests\models\runner\plugins\PluginRegistry;
use oat\taoTests\models\runner\plugins\TestPluginService;

class ProgressBar extends TestRunnerFeature {

    public function __construct() {
        $serviceManager = ServiceManager::getServiceManager();
        $pluginService = $serviceManager->get(TestPluginService::CONFIG_ID);

        parent::__construct(
            'progressBar',
            ['progressbar'],
            true,
            $pluginService->getAllPlugins()
        );
    }

    public function getLabel() {
        return __('Progress bar');
    }

    public function getDescription() {
        return __('Display the current progression within the test');
    }
}