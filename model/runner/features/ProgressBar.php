<?php

namespace oat\taoInvalsi\model\runner\features;

use oat\taoTests\models\runner\features\TestRunnerFeature;

class ProgressBar extends TestRunnerFeature {

    public function getId() {
        return 'progressBar';
    }

    public function getPluginsIds() {
        return ['progressbar'];
    }

    public function getLabel() {
        return __('Progress bar');
    }

    public function getDescription() {
        return __('Display the current progression within the test');
    }

    public function isEnabledByDefault() {
        return true;
    }
}