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
 *               
 * 
 */

namespace oat\taoInvalsi\scripts\update;

use \common_ext_ExtensionUpdater;
use oat\tao\model\theme\ThemeService;
use oat\taoInvalsi\model\theme\TaoInvalsiDefaultTheme;
use oat\taoQtiItem\model\HookRegistry;
use oat\taoQtiTest\models\TestRunnerClientConfigRegistry;

class Updater extends common_ext_ExtensionUpdater
{

    /**
     * @param string $initialVersion
     * @return string $versionUpdatedTo
     */
    public function update($initialVersion)
    {
        if($this->isVersion('1.0.0')){
            HookRegistry::getRegistry()->set('taoInvalsiCreator', 'oat\taoInvalsi\model\CreatorHook');
            $this->setVersion('1.1.0');
        }
        if($this->isVersion('1.1.0')){
            $themeService = $this->getServiceManager()->get(ThemeService::SERVICE_ID);

            $themeService->setTheme(new TaoInvalsiDefaultTheme());
            $this->getServiceManager()->register(ThemeService::SERVICE_ID, $themeService);

            $this->setVersion('1.2.0');
        }

        if ($this->isVersion('1.2.0')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['disableExitMessage'] = array(
                'test' => true,
                'testPart' => true,
                'testSection' => true,

            );
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.3.0');
        }
        
        if ($this->isVersion('1.3.0')) {
            $registry = TestRunnerClientConfigRegistry::getRegistry();

            $registry->registerQtiTools('invalsiDisabler', array(
                'hook' => 'taoInvalsi/actionBar/disabler/disabler'
            ));

            $this->setVersion('1.4.0');
        }

        if ($this->isVersion('1.4.0')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['disableExitMessage'] = array(
                'test' => false,
                'testPart' => false,
                'testSection' => false
            );
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.5.0');
        }
    }
}
