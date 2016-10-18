<?php

namespace oat\taoInvalsi\model\runner\features;

use oat\oatbox\service\ServiceManager;
use oat\taoTests\models\runner\features\TestRunnerFeature;
use oat\taoTests\models\runner\plugins\TestPluginService;

class Accessibility extends TestRunnerFeature {

    public function __construct() {
        $serviceManager = ServiceManager::getServiceManager();
        $pluginService = $serviceManager->get(TestPluginService::CONFIG_ID);

        parent::__construct(
            'accessibility',
            [/*'itemThemeSwitcher',*/ 'disableRightClick'/*, 'zoom'*/],
            true,
            $pluginService->getAllPlugins()
        );
    }

    public function getLabel() {
        return __('Accessibility');
    }

    public function getDescription() {
        return __('a bunch of very interesting tools !');
    }
}
