<?php

/**
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA.
 */

namespace oat\taoInvalsi\scripts\update;

use \common_ext_ExtensionUpdater;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\theme\ThemeService;
use oat\taoInvalsi\model\theme\TaoInvalsiDefaultTheme;
use oat\taoInvalsi\scripts\install\RegisterTestPluginService;
use oat\taoInvalsi\scripts\install\RegisterTestRunnerFeatures;
use oat\taoInvalsi\scripts\install\RegisterTestRunnerPlugins;
use oat\taoInvalsi\scripts\install\SetNewTestRunner;
use oat\taoInvalsi\scripts\install\SetNewTestRunnerConfig;
use oat\taoInvalsi\scripts\install\SetPasswordPolicy;
use oat\taoQtiItem\model\HookRegistry;
use oat\taoQtiTest\models\TestRunnerClientConfigRegistry;
use oat\taoQtiItem\model\QtiCreatorClientConfigRegistry;
use oat\tao\model\mvc\DefaultUrlService;

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
        $this->skip('3.0.0', '4.1.1');
    }

}
