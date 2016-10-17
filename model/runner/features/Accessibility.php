<?php

namespace oat\taoInvalsi\model\runner\features;

use oat\taoTests\models\runner\features\TestRunnerFeature;

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
        return __('a bunch of very interesting tools !');
    }

    public function isEnabledByDefault() {
        return false;
    }
}
