<?php

/**
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA.
 */

namespace oat\taoInvalsi\scripts\update;

use \common_ext_ExtensionUpdater;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoInvalsi\model\theme\TaoInvalsiDefaultTheme;
use oat\taoInvalsi\scripts\install\RegisterTestRunnerFeatures;
use oat\taoInvalsi\scripts\install\RegisterTestRunnerPlugins;
use oat\taoInvalsi\scripts\install\SetNewTestRunnerConfig;
use oat\taoInvalsi\scripts\install\SetPasswordPolicy;
use oat\taoInvalsi\scripts\install\SetupBookletConfigService;
use oat\taoQtiItem\model\HookRegistry;
use oat\taoQtiItem\model\ItemModel;
use oat\taoQtiItem\model\QtiCreatorClientConfigRegistry;
use oat\taoQtiTest\models\TestRunnerClientConfigRegistry;
use oat\taoQtiTest\scripts\install\SetNewTestRunner;
use oat\taoTests\models\runner\plugins\PluginRegistry;
use oat\taoTests\models\runner\plugins\TestPlugin;
use oat\taoTests\models\runner\plugins\TestPluginService;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\model\theme\ThemeService;

class Updater extends common_ext_ExtensionUpdater {

    /**
     * @param string $initialVersion
     * @return string $versionUpdatedTo
     */
    public function update($initialVersion) {
        if ($this->isVersion('1.0.0')) {
            HookRegistry::getRegistry()->set('taoInvalsiCreator', 'oat\taoInvalsi\model\CreatorHook');
            $this->setVersion('1.1.0');
        }
        if ($this->isVersion('1.1.0')) {
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
        $this->skip('1.5.0', '1.5.1');

        if ($this->isVersion('1.5.1')) {
            $setNewTestRunner = new SetNewTestRunner();
            $setNewTestRunner([]);

            $registerTestRunnerPlugins = new RegisterTestRunnerPlugins();
            $registerTestRunnerPlugins([]);

            $this->setVersion('1.6.0');
        }

        if ($this->isVersion('1.6.0')) {
            $setNewTestRunnerConfig = new SetNewTestRunnerConfig();
            $setNewTestRunnerConfig([]);

            $this->setVersion('1.6.1');
        }
        $this->skip('1.6.1', '1.6.2');

        if ($this->isVersion('1.6.2')) {
            $registry = QtiCreatorClientConfigRegistry::getRegistry();
            $registry->registerPlugin('cssBlocks', 'taoInvalsi/qtiCreator/plugins/panel/cssBlocks', 'panel');
            $this->setVersion('1.6.3');
        }
        $this->skip('1.6.3', '1.6.4');

        if ($this->isVersion('1.6.4')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');

            $config = $extension->getConfig('testRunner');
            $config['check-informational'] = true;

            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.7.0');
        }

        $this->skip('1.7.0', '1.7.2');

        if ($this->isVersion('1.7.2')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');

            $config = $extension->getConfig('testRunner');
            $config['force-branchrules'] = true;
            $config['path-tracking'] = true;
            $config['always-allow-jumps'] = true;

            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.8.0');
        }

        if ($this->isVersion('1.8.0')) {
            $registerTestRunnerFeatures = new RegisterTestRunnerFeatures();
            $registerTestRunnerFeatures->setServiceLocator($this->getServiceManager());
            $registerTestRunnerFeatures([]);

            $this->setVersion('1.9.0');
        }

        if ($this->isVersion('1.9.0')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');

            $config = $extension->getConfig('testRunner');
            $config['keep-timer-up-to-timeout'] = true;

            $extension->setConfig('testRunner', $config);
            $this->setVersion('1.10.0');
        }

        $this->skip('1.10.0', '1.13.0');

        if ($this->isVersion('1.13.0')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['test-taker-review-force-title'] = false;
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.14.0');
        }

        $this->skip('1.14.0', '1.14.1');

        if ($this->isVersion('1.14.1')) {
            $registerTestRunnerPlugins = new RegisterTestRunnerPlugins();
            $registerTestRunnerPlugins([]);

            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['plugins']['formula'] = [
                'documentUrl' => '/taoInvalsi/views/js/runner/plugins/tools/formula/sample.pdf'
            ];
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.15.0');
        }

        $this->skip('1.15.0', '1.16.0');

        if ($this->isVersion('1.16.0')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['plugins']['formula'] = [
                'documentUrl' => '/taoInvalsi/views/js/runner/plugins/tools/formula/formulario.pdf'
            ];
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.16.1');
        }

        if ($this->isVersion('1.16.1')) {

            \oat\tao\model\accessControl\func\AclProxy::applyRule(new \oat\tao\model\accessControl\func\AccessRule('grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole', array('ext' => 'taoInvalsi', 'mod' => 'Main', 'act' => 'login')));

            $this->setVersion('1.17.0');
        }
        $this->skip('1.17.0', '1.17.1');

        if ($this->isVersion('1.17.1')) {
            $service = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);
            $service->createFileSystem('taoMonitoring');
            $this->getServiceManager()->register(FileSystemService::SERVICE_ID, $service);

            $this->setVersion('1.18.1');
        }

        if ($this->isVersion('1.18.1')) {
            $setPasswordPolicy = new SetPasswordPolicy();
            $setPasswordPolicy([]);

            $this->setVersion('1.19.0');
        }

        $this->skip('1.19.0', '1.24.0');

        if ($this->isVersion('1.24.0')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['plugins']['formula'] = [
                'documentUrl' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L13.pdf'
            ];
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.25.0');
        }

        if ($this->isVersion('1.25.0')) {
            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');

            $config = $extension->getConfig('testRunner');
            $config['plugins']['calculator'] = array(
                'template' => 'taoInvalsi/runner/plugins/tools/calculator/template.tpl'
            );

            $extension->setConfig('testRunner', $config);
            $this->setVersion('1.26.0');
        }

        $this->skip('1.26.0', '2.1.0');

        if ($this->isVersion('2.1.0')) {
            $defaultUrlService = $this->getServiceManager()->get(DefaultUrlService::SERVICE_ID);

            if ($defaultUrlService->getOption('login') == ['ext' => 'taoInvalsi', 'controller' => 'Main', 'action' => 'login']) {
                $defaultUrlService->setOption(
                    'login',
                    [ 'ext' => 'tao', 'controller' => 'Main', 'action' => 'login']
                );
                $this->getServiceManager()->register(DefaultUrlService::SERVICE_ID , $defaultUrlService);
            }

            $this->setVersion('3.0.0');
        }
        $this->skip('3.0.0', '6.0.0');

        if ($this->isVersion('6.0.0')) {

            $this->runExtensionScript(SetupBookletConfigService::class);

            $this->setVersion('6.1.0');
        }

        if ($this->isVersion('6.1.0')) {

            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['plugins']['formula'] = [
                'documentUrl' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L13.pdf',
                'documentUrlL10' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L10.pdf',
                'documentUrlL13' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L13.pdf'
            ];
            $extension->setConfig('testRunner', $config);

            $this->setVersion('6.2.0');
        }

        if($this->isVersion('6.2.0')){

            if(\common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiItem')->hasConfig(ItemModel::COMPILER)){
                $compilerConfig = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiItem')->getConfig(ItemModel::COMPILER);
                /** @var ItemModel $itemModelService */
                $itemModelService = $this->getServiceManager()->get(ItemModel::SERVICE_ID);
                $itemModelService->setOption(ItemModel::COMPILER, $compilerConfig);
                $this->getServiceManager()->register(ItemModel::SERVICE_ID, $itemModelService);
                \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiItem')->unsetConfig(ItemModel::COMPILER);
            }

            $this->setVersion('6.3.0');
        }

        $this->skip('6.3.0', '6.4.0');

        if($this->isVersion('6.4.0')){
            $registry = PluginRegistry::getRegistry();
            foreach($registry->getMap() as $module => $plugin){
                if(preg_match("/^taoInvalsi/", $module) && is_null($plugin['bundle'])){
                    $plugin['bundle'] = 'taoInvalsi/loader/testPlugins.min';
                    $registry->register(TestPlugin::fromArray($plugin));
                }
            }
            $this->setVersion('6.5.0');
        }

        $this->skip('6.5.0', '6.6.0');

        if($this->isVersion('6.6.0')) {
            $taoQtiItem = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiItem');
            $config = ['multi-column' => true];
            $taoQtiItem->setConfig('qtiCreator', $config);
            $this->setVersion('6.6.1');
        }

        $this->skip('6.6.1', '6.7.1');

        if($this->isVersion('6.7.1')){
            $pluginService = $this->getServiceManager()->get(TestPluginService::SERVICE_ID);
            $plugin = $pluginService->getPlugin('warnBeforeLeaving');
            //either register or activate the "warning plugin"
            if(is_null($plugin)){
                $pluginService->registerPlugins([[
                    'id' => 'warnBeforeLeaving',
                    'name' => 'Warn before leaving',
                    'module' => 'taoQtiTest/runner/plugins/navigation/warnBeforeLeaving',
                    'bundle' => 'taoQtiTest/loader/testPlugins.min',
                    'description' => 'Warn the test taker when closing the browser',
                    'category' => 'navigation',
                    'active' => true,
                    'tags' => [ ]
                ]]);
            } else {
                $pluginService->activatePlugin($plugin);
            }
            $this->setVersion('6.8.0');
        }

        $this->skip('6.8.0', '6.8.1');

        if ($this->isVersion('6.8.1')) {

            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['enable-allow-skipping'] = false;
            $config['enable-validate-responses'] = false;
            $extension->setConfig('testRunner', $config);

            $this->setVersion('6.8.2');
        }

        $this->skip('6.8.2', '6.9.0');
    }

}
