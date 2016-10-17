<?php

namespace oat\taoInvalsi\model\runner\plugins;

use oat\taoTests\models\runner\plugins\TestRunnerFeature;

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
        return __('xxxx');
    }

    public function isEnabledByDefault() {
        return true;
    }
}