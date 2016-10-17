<?php

namespace oat\taoInvalsi\model\runner\plugins;

use oat\taoTests\models\runner\plugins\TestRunnerFeature;

class Accessibility extends TestRunnerFeature {

    public function getId() {
        return 'accessibility';
    }

    public function getPluginsIds() {
        return ['itemThemeSwitcher', 'disableRightClick', 'zoom'];
    }

    public function getLabel() {
        return __('Accessibility');
    }

    public function getDescription() {
        return __('xxxx');
    }

    public function isEnabledByDefault() {
        return false;
    }
}
