<?php

namespace oat\taoInvalsi\model\runner\features;

use oat\taoTests\models\runner\features\TestRunnerFeature;
use oat\taoTests\models\runner\plugins\PluginRegistry;

class ProgressBar extends TestRunnerFeature {

    public function __construct() {
        parent::__construct(
            'progressBar',
            ['progressbar'],
            true,
            PluginRegistry::getRegistry()
        );
    }

    public function getLabel() {
        return __('Progress bar');
    }

    public function getDescription() {
        return __('Display the current progression within the test');
    }
}