<?php
/**
 * ***********************************************************************
 * INVALSI CONFIDENTIAL
 * __________________
 *
 *  All Rights Reserved.
 *
 *  NOTICE:  All information contained herein is, and remains
 *  the property of INVALSI and its suppliers, if any.  The
 *  intellectual and technical concepts contained herein are
 *  proprietary to INVALSI and its suppliers, and are protected
 *  by trade secret or copyright law.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from INVALSI.
 *  ************************************************************************
 */

namespace oat\taoInvalsi\scripts\install;

use common_ext_action_InstallAction;
use oat\taoQtiItem\model\QtiCreatorClientConfigRegistry;

class SetQtiCreatorConfig extends common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $registry = QtiCreatorClientConfigRegistry::getRegistry();
        $registry->registerPlugin('cssBlocks', 'taoInvalsi/qtiCreator/plugins/panel/cssBlocks', 'panel');

        $taoQtiItem = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiItem');
        $config = ['multi-column' => true];
        $taoQtiItem->setConfig('qtiCreator', $config);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Qti Creator\'s Plugins settings added to Tao Qti Item extension');
    }
}
