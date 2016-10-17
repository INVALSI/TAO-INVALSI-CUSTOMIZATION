<?php

namespace oat\taoInvalsi\model\runner\features;

use oat\taoTests\models\runner\features\TestRunnerFeature;
use oat\taoTests\models\runner\plugins\PluginRegistry;

class Accessibility extends TestRunnerFeature {

    public function __construct() {
        parent::__construct(
            'accessibility',
            ['itemThemeSwitcher', 'disableRightClick', 'zoom'],
            true,
            PluginRegistry::getRegistry()
        );
    }

    public function getLabel() {
        return __('Accessibility');
    }

    public function getDescription() {
        return __('a bunch of very interesting tools !');
    }
}
