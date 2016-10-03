<?php
namespace oat\taoInvalsi\scripts\install;

use common_ext_ExtensionsManager;

class SetNewTestRunnerConfig extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $invalsiConfig = array(
            'check-informational' => true,
            'test-taker-review' => true,
            'test-taker-review-region' => 'left',
            'test-taker-review-force-title' => true,
            'test-taker-review-item-title' => 'Item %d', // forced translation
            'test-taker-review-scope' => 'testSection',
            'test-taker-review-prevents-unseen' => true,
            'test-taker-review-can-collapse' => true,
            'force-branchrules' => true,
            'path-tracking' => true,
            'always-allow-jumps' => true
        );

        $qtiTest = common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
        $config = $qtiTest->getConfig('testRunner');
        $config = array_merge($config, $invalsiConfig);
        $qtiTest->setConfig('testRunner', $config);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Test runner config saved');
    }
}
