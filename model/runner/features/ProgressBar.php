<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
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
