<?php
namespace oat\taoInvalsi\scripts\install;

use common_ext_ExtensionsManager;

class SetPasswordPolicy extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $invalsiConfig = array(
            'constrains' => array(
                'length' => 6,
                'upper' => false,
                'lower' => false,
                'spec' => false,
            ),
        );

        $generis = common_ext_ExtensionsManager::singleton()->getExtensionById('generis');
        $config = $generis->getConfig('passwords');
        $config = array_merge($config, $invalsiConfig);
        $generis->setConfig('passwords', $config);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Password Policy changed.');
    }
}
