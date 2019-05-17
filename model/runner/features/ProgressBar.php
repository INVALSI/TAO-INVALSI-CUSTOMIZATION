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

namespace oat\taoInvalsi\model\runner\features;

use oat\oatbox\service\ServiceManager;
use oat\taoTests\models\runner\features\TestRunnerFeature;
use oat\taoTests\models\runner\plugins\TestPluginService;

/**
 * Allows to toggle Progress bar feature per delivery
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
class ProgressBar extends TestRunnerFeature {

    public function __construct() {
        $serviceManager = ServiceManager::getServiceManager();
        $pluginService = $serviceManager->get(TestPluginService::CONFIG_ID);

        parent::__construct(
            'progressBar',
            ['progressbar'],
            true,
            $pluginService->getAllPlugins()
        );
    }

    public function getLabel() {
        return __('Progress bar');
    }

    public function getDescription() {
        return __('Display the current progression within the test');
    }
}
