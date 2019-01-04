<?php

/**
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA.
 */

namespace oat\taoInvalsi\scripts\update;

use \common_ext_ExtensionUpdater;
use oat\oatbox\event\EventManager;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\event\LogoutSucceedEvent;
use oat\taoDeliveryRdf\scripts\ToggleGuestAccess;
use oat\taoInvalsi\model\session\Capture;
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
use oat\taoQtiTest\scripts\install\DisableAllowShortcut;
use oat\taoQtiTest\scripts\install\DisableConnectivity;
use oat\taoTests\models\runner\plugins\PluginRegistry;
use oat\taoTests\models\runner\plugins\TestPlugin;
use oat\taoTests\models\runner\plugins\TestPluginService;
use oat\tao\model\mvc\DefaultUrlService;
use oat\tao\model\theme\ThemeService;
use oat\taoQtiTest\scripts\install\SetPreCachingConfig;
use oat\taoQtiTest\scripts\install\SetGuidedNavigation;
use oat\taoDelivery\model\AssignmentService;
use oat\taoDeliveryRdf\model\GroupAssignment;
use oat\taoInvalsi\scripts\install\RegisterRdsLaunchLog;
use oat\taoInvalsi\scripts\install\EnableGdprMessage;

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
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
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
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
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
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');

            $config = $extension->getConfig('testRunner');
            $config['check-informational'] = true;

            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.7.0');
        }

        $this->skip('1.7.0', '1.7.2');

        if ($this->isVersion('1.7.2')) {
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');

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
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');

            $config = $extension->getConfig('testRunner');
            $config['keep-timer-up-to-timeout'] = true;

            $extension->setConfig('testRunner', $config);
            $this->setVersion('1.10.0');
        }

        $this->skip('1.10.0', '1.13.0');

        if ($this->isVersion('1.13.0')) {
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['test-taker-review-force-title'] = false;
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.14.0');
        }

        $this->skip('1.14.0', '1.14.1');

        if ($this->isVersion('1.14.1')) {
            $registerTestRunnerPlugins = new RegisterTestRunnerPlugins();
            $registerTestRunnerPlugins([]);

            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['plugins']['formula'] = [
                'documentUrl' => '/taoInvalsi/views/js/runner/plugins/tools/formula/sample.pdf'
            ];
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.15.0');
        }

        $this->skip('1.15.0', '1.16.0');

        if ($this->isVersion('1.16.0')) {
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
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
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['plugins']['formula'] = [
                'documentUrl' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L13.pdf'
            ];
            $extension->setConfig('testRunner', $config);

            $this->setVersion('1.25.0');
        }

        if ($this->isVersion('1.25.0')) {
            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');

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

            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
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

            if($this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiItem')->hasConfig(ItemModel::COMPILER)){
                $compilerConfig = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiItem')->getConfig(ItemModel::COMPILER);
                /** @var ItemModel $itemModelService */
                $itemModelService = $this->getServiceManager()->get(ItemModel::SERVICE_ID);
                $itemModelService->setOption(ItemModel::COMPILER, $compilerConfig);
                $this->getServiceManager()->register(ItemModel::SERVICE_ID, $itemModelService);
                $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiItem')->unsetConfig(ItemModel::COMPILER);
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
            $taoQtiItem = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiItem');
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

            $extension = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
            $config = $extension->getConfig('testRunner');
            $config['enable-allow-skipping'] = false;
            $config['enable-validate-responses'] = false;
            $extension->setConfig('testRunner', $config);

            $this->setVersion('6.8.2');
        }

        $this->skip('6.8.2', '6.10.0');

        if ($this->isVersion('6.10.0')) {

            // Disable feature in Test Runner not required for this project.
            $script = new DisableAllowShortcut();
            $this->getServiceManager()->propagate($script);
            $script([]);

            $script = new DisableConnectivity();
            $this->getServiceManager()->propagate($script);
            $script([]);

            $this->setVersion('6.11.0');
        }

        $this->skip('6.11.0', '6.12.0');

        if ($this->isVersion('6.12.0')) {

            $extensionManager = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID);
            if ($extensionManager->isInstalled('taoClientDiagnostic')) {
                $extension = $extensionManager->getExtensionById('taoClientDiagnostic');
                $config = $extension->getConfig('clientDiag');

                if (!isset($config['testers']) || !isset($config['testers']['intensive_bandwidth'])) {
                    $config['testers']['intensive_bandwidth'] = [
                        'enabled' => false,
                        'level' => 2,
                        'tester' => 'taoClientDiagnostic/tools/bandwidth/tester',
                        'unit' => 1.2,
                        'ideal' => 45,
                        'max' => 100
                    ];
                }

                $config['testers']['intensive_bandwidth']['enabled'] = true;

                $extension->setConfig('clientDiag', $config);
            }

            $this->setVersion('6.13.0');
        }

        if ($this->isVersion('6.13.0')) {

            //enables the precaching config (the proxy)
            $setPrecachingConfig = new SetPreCachingConfig();
            $setPrecachingConfig->setServiceLocator($this->getServiceManager());
            $setPrecachingConfig([]);

            $qtiTest = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
            $config = $qtiTest->getConfig('testRunner');

            //number of item to cache
            $config['item-cache-size'] = 3;

            //enable the communicator, but does not define grouped actions
            $config['bootstrap']['communication'] = [
                'enabled'     => true,
                'type'        => 'poll',
                'extension'   => 'taoQtiTest',
                'controller'  => 'Runner',
                'action'      => 'messages',
                'syncActions' => [],
                'params'      => []
            ];

            $qtiTest->setConfig('testRunner', $config);

            $this->setVersion('6.14.0');
        }

        if ($this->isVersion('6.14.0')) {

            $qtiTest = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
            $config = $qtiTest->getConfig('testRunner');


            // use the request provider for the communicator
            $config['bootstrap']['communication']['type'] = 'request';

            //disable the connectivity indicator
            $config['plugins']['connectivity'] = [
                'indicator' => false
            ];

            $qtiTest->setConfig('testRunner', $config);

            //reactivate the connectivity plugin, but with the indicator disabled
            $pluginService = $this->getServiceManager()->get(TestPluginService::SERVICE_ID);
            $plugin = $pluginService->getPlugin('connectivity');
            if(is_null($plugin)){
                $pluginService->registerPlugins([[
                    'id' => 'connectivity',
                    'name' => 'Connectivity check',
                    'module' => 'taoQtiTest/runner/plugins/controls/connectivity/connectivity',
                    'bundle' => 'taoQtiTest/loader/testPlugins.min',
                    'description' => 'Pause the test when the network loose the connection',
                    'category' => 'controls',
                    'active' => true,
                    'tags' => [ 'core', 'technical' ]
                ]]);
            } else {
                $pluginService->activatePlugin($plugin);
            }

            $this->setVersion('6.15.0');
        }

        $this->skip('6.15.0', '6.16.0');

        if ($this->isVersion('6.16.0')) {
            $extensionManager = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID);
            if ($extensionManager->isInstalled('taoClientDiagnostic')) {
                $extension = $extensionManager->getExtensionById('taoClientDiagnostic');
                $config = $extension->getConfig('clientDiag');
                $config['customInput']['istituto'] = 'school';
                $extension->setConfig('clientDiag', $config);
            }

            $this->setVersion('6.17.0');
        }

        if ($this->isVersion('6.17.0')) {
            $qtiTest = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
            $config = $qtiTest->getConfig('testRunner');
            $config['test-taker-review-force-title'] = true;
            $config['test-taker-review-item-title'] = 'Domanda %d';

            $qtiTest->setConfig('testRunner', $config);
            $this->setVersion('6.18.0');
        }

        $this->skip('6.18.0', '6.20.0');

        if ($this->isVersion('6.20.0')) {

            $this->getServiceManager()->register(Capture::SERVICE_ID, new Capture([Capture::OPTION_PERSISTENCE => 'cache']));

            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(LoginSucceedEvent::class,[Capture::SERVICE_ID,'storeLogin']);
            $eventManager->attach(LogoutSucceedEvent::class,[Capture::SERVICE_ID,'storeLogout']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('6.21.0');
        }

        if ($this->isVersion('6.21.0')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['test-taker-review-force-informational-title'] = true;
            $config['test-taker-review-informational-item-title'] = 'Istruzioni';

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('6.22.0');
        }

        $this->skip('6.22.0', '6.23.0');

        if ($this->isVersion('6.23.0')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['plugins']['textToSpeech']['language'] = 'it-IT';

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('6.24.0');
        }

        if ($this->isVersion('6.24.0')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['test-taker-review-scope'] = 'testPart';

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('6.25.0');
        }

        if ($this->isVersion('6.25.0')) {

            //enables the precaching config (the proxy)
            $setGuidedNavigation = new SetGuidedNavigation();
            $setGuidedNavigation->setServiceLocator($this->getServiceManager());
            $setGuidedNavigation([]);

            $this->setVersion('6.26.0');
        }

        if ($this->isVersion('6.26.0')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['plugins']['textToSpeech']['setupUrl'] = '//taotoolbar.speechstream.net/tao/configInvQA211.js';

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('6.27.0');
        }

        if ($this->isVersion('6.27.0')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['plugins']['textToSpeech']['overrideMathExpr'] = "Please watch the formula";

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('6.28.0');
        }

        if ($this->isVersion('6.28.0')) {

            $pluginService = $this->getServiceManager()->get(TestPluginService::SERVICE_ID);
            $pluginService->registerPlugins([[
                'id' => 'answerCache',
                'name' => 'Answers cache',
                'module' => 'taoInvalsi/runner/plugins/content/answerCache',
                'bundle' => 'taoInvalsi/loader/testPlugins.min',
                'description' => 'Cache the answers to restore them after refresh',
                'category' => 'content',
                'active' => true,
                'tags' => [ ]
            ]]);
            $this->setVersion('6.29.0');
        }

        if ($this->isVersion('6.29.0')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['plugins']['textToSpeech']['overrideMathExpr'] = "Guarda la formula";

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('6.29.1');
        }

        $this->skip('6.29.1', '6.31.2');

        if ($this->isVersion('6.31.2')) {
            $this->runExtensionScript(ToggleGuestAccess::class, ['off']);

            $this->setVersion('6.32.0');
        }

        $this->skip('6.32.0', '6.33.0');

        if ($this->isVersion('6.33.0')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['enable-allow-skipping'] = true;

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('6.34.0');
        }

        $this->skip('6.34.0', '6.34.1');

        if ($this->isVersion('6.34.1')) {
            ClientLibConfigRegistry::getRegistry()->register(
                'layout/tree/loader', array(
                    'treeProvider' => 'resource-selector'
                )
            );

            $this->setVersion('6.35.0');
        }

        if ($this->isVersion('6.35.0')) {

            $pluginService = $this->getServiceManager()->get(TestPluginService::SERVICE_ID);
            $pluginService->registerPlugins([[
                'id' => 'preventDropToInput',
                'name' => 'Prevent Drop To Input',
                'module' => 'taoTestRunnerPlugins/runner/plugins/tools/preventDropToInput',
                'bundle' => 'taoTestRunnerPlugins/loader/testPlugins.min',
                'description' => 'Prevent images movement to inputs',
                'category' => 'tools',
                'active' => true,
                'tags' => []
            ]]);
            $this->setVersion('6.36.0');
        }

        if ($this->isVersion('6.36.0')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['test-taker-review-force-informational-title'] = true;
            $config['test-taker-review-informational-item-title'] = false;

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('6.37.0');
        }

        $this->skip('6.37.0', '7.0.0');

        if ($this->isVersion('7.0.0')) {
            /** @var GroupAssignment $assignment */
            $assignment = $this->getServiceManager()->get(AssignmentService::SERVICE_ID);
            $assignment->setOption(GroupAssignment::DISPLAY_DATES_OPTION, false);
            $this->getServiceManager()->register(AssignmentService::SERVICE_ID, $assignment);
            $this->setVersion('7.1.0');
        }

        if ($this->isVersion('7.1.0')) {
            $this->addReport(\common_report_Report::createInfo('launch log service need to be enabled (see '.RegisterRdsLaunchLog::class.')'));
            $this->setVersion('7.2.0');
        }

        $this->skip('7.2.0', '7.2.1');

        if ($this->isVersion('7.2.1')) {
            $registerTestRunnerFeatures = new RegisterTestRunnerFeatures();
            $registerTestRunnerFeatures->setServiceLocator($this->getServiceManager());
            $registerTestRunnerFeatures([]);

            $this->setVersion('7.2.2');
        }

        if ($this->isVersion('7.2.2')) {
            $qtiTestService = $this->getServiceManager()->get('taoQtiTest/testRunner');
            $config = $qtiTestService ->getConfig();

            $config['plugins']['textToSpeech']['setupUrl'] = '//taotoolbar.speechstream.net/tao/configInvQA214.js';

            $qtiTestService->setOption('config', $config);
            $this->getServiceManager()->register('taoQtiTest/testRunner', $qtiTestService);

            $this->setVersion('7.3.0');
        }

        if ($this->isVersion('7.3.0')) {
            ClientLibConfigRegistry::getRegistry()->register(
                'taoDelivery/controller/DeliveryServer/index',
                array('extraRoutes' => ['taoInvalsi/Delivery/userMenu'])
            );
            ClientLibConfigRegistry::getRegistry()->register(
                'taoQtiTest/controller/runner/runner',
                array('extraRoutes' => ['taoInvalsi/Delivery/userMenu'])
            );
    
            $this->setVersion('7.4.0');
        }

        $this->skip('7.4.0', '7.4.1');

        if ($this->isVersion('7.4.1')) {
            $loginService = $this->getServiceManager()->get('tao/login');
            $config = $loginService->getConfig();
            $config['show_gdpr'] = true;
            $loginService->setOption('config', $config);
            $this->getServiceManager()->register('tao/login', $loginService);

            $this->setVersion('7.5.0');
        }
        
        if ($this->isVersion('7.5.0')) {

            $pluginService = $this->getServiceManager()->get(TestPluginService::SERVICE_ID);
            $pluginService->registerPlugins([[
                'id' => 'tableChoiceHelper',
                'name' => 'Table Choice Helper',
                'module' => 'taoInvalsi/runner/plugins/content/tableChoiceHelper',
                'bundle' => 'taoInvalsi/loader/testPlugins.min',
                'description' => 'Adds a CSS selector to each first choice interaction of each block',
                'category' => 'content',
                'active' => true,
                'tags' => [ ]
            ]]);
            $this->setVersion('7.5.1');
        }

        $this->skip('7.5.1', '7.5.3');

        if ($this->isVersion('7.5.3')) {
            // We distinguish which route called the file by adding a param:
            ClientLibConfigRegistry::getRegistry()->register(
                'taoDelivery/controller/DeliveryServer/index',
                array('extraRoutes' => ['taoInvalsi/Delivery/userMenu?page=delivery'])
            );
            ClientLibConfigRegistry::getRegistry()->register(
                'taoQtiTest/controller/runner/runner',
                array('extraRoutes' => ['taoInvalsi/Delivery/userMenu?page=testRunner'])
            );
    
            $this->setVersion('7.5.4');
        }

    }
}
