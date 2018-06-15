<?php
/**
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA.
 */

use oat\taoMonitoring\scripts\install\RegisterAwsFileSystem;
use oat\taoInvalsi\scripts\install\SetupBookletConfigService;
use oat\taoInvalsi\scripts\install\SetQtiCreatorConfig;
use oat\taoInvalsi\scripts\install\SetNewTestRunnerConfig;
use oat\taoInvalsi\scripts\install\RegisterTestRunnerPlugins;
use oat\taoInvalsi\scripts\install\RegisterTestRunnerFeatures;
use oat\taoInvalsi\scripts\install\SetPasswordPolicy;
use oat\taoQtiTest\scripts\install\DisableAllowShortcut;
use oat\taoInvalsi\scripts\install\ConfigureClientDiagnostic;
use oat\taoQtiTest\scripts\install\SetPreCachingConfig;
use oat\taoQtiTest\scripts\install\SetGuidedNavigation;
use oat\taoDeliveryRdf\scripts\ToggleGuestAccess;

return array(
    'name' => 'taoInvalsi',
    'label' => 'TAO INVALSI',
    'description' => 'Custom code for Invalsi',
    'license' => 'Proprietary',
    'version' => '6.32.1',
	'author' => 'Open Assessment Technologies SA',
	'requires' => array(
	    'generis' => '>=7.6.0',
        'taoCe' => '>=1.2.0',
        'tao' => '>=16.4.0',
        'taoMediaManager' => '*',
        'taoQtiTest' => '>=24.0.0',
        'taoQtiItem' => '>=13.3.2',
        'taoTextHelp' => '>=0.4.0',
        'taoTests' => '>=3.5.0',
        'pciSamples' => '>=1.3.0',
        'taoBooklet' => '>=1.1.0',
        'taoMonitoring' => '>=0.2.0',
        'taoDacSimple' => '>=2.0.1',
        'taoDeliveryRdf' => '>=3.12.0',
        'taoDelivery' => '>=9.3.0'
    ),
	'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoInvalsiManager',
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoInvalsiManager', array('ext'=>'taoInvalsi'))
    ),
    'install' => array(
        'php' => array(
            __DIR__.'/scripts/install/setThemeConfig.php',
            __DIR__ . '/scripts/install/addHook.php',
            SetQtiCreatorConfig::class,
            \oat\taoQtiTest\scripts\install\SetNewTestRunner::class,
            SetNewTestRunnerConfig::class,
            RegisterTestRunnerPlugins::class,
            RegisterTestRunnerFeatures::class,
            SetPasswordPolicy::class,
            RegisterAwsFileSystem::class,
            SetupBookletConfigService::class,
            DisableAllowShortcut::class,
            ConfigureClientDiagnostic::class,
            SetPreCachingConfig::class,
            SetGuidedNavigation::class,
            [ToggleGuestAccess::class, ['off']]
        )
    ),
    'update' => 'oat\\taoInvalsi\\scripts\\update\\Updater',
    'uninstall' => array(
    ),
    'routes' => array(
        '/taoInvalsi' => 'oat\\taoInvalsi\\controller'
    ),
	'constants' => array(
	    # views directory
	    "DIR_VIEWS" => dirname(__FILE__).DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR,

        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL.'taoInvalsi/',
	),
    'extra' => array(
        'structures' => dirname(__FILE__).DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'structures.xml',
    )
);
