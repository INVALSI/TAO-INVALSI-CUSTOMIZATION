<?php
/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 */

namespace oat\taoInvalsi\scripts\install;

use oat\taoInvalsi\model\runner\plugins\InvalsiTestPluginService;
use oat\taoTests\models\runner\plugins\TestPluginService;

class RegisterTestPluginService extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $old = $this->getServiceManager()->get(TestPluginService::CONFIG_ID);
        $testPluginService = new InvalsiTestPluginService($old->getOptions());
        $this->registerService(TestPluginService::CONFIG_ID, $testPluginService);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'test plugin service registration successful');
    }
}
