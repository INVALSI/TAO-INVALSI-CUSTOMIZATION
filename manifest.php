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

use oat\tao\scripts\install\SetResourceSelector;
use oat\taoInvalsi\scripts\install\SetupEventsCapture;
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
use oat\taoInvalsi\scripts\install\addAssignmentSettings;
use oat\taoInvalsi\scripts\install\SetClientRouterConfig;
use oat\taoInvalsi\scripts\install\EnableGdprMessage;
use oat\taoInvalsi\scripts\install\RegisterInvalsiTestCategoryPresetProvider;

return array(
    'name' => 'taoInvalsi',
    'label' => 'TAO INVALSI',
    'description' => 'Custom code for Invalsi',
    'license' => 'Proprietary',
    'version' => '10.6.0',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'generis'         => '>=7.6.0',
        'taoCe'           => '>=5.0.0',
        'tao'             => '>=22.14.2',
        'taoMediaManager' => '*',
        'taoQtiTest'      => '>=29.3.0',
        'taoQtiItem'      => '>=18.0.0',
        'taoTextHelp'     => '>=1.3.3',
        'taoTests'        => '>=8.0.0',
        'pciSamples'      => '>=1.3.0',
        'taoBooklet'      => '>=2.0.0',
        'taoMonitoring'   => '>=2.0.0',
        'taoDacSimple'    => '>=3.0.0',
        'taoDeliveryRdf'  => '>=7.2.1',
        'taoDelivery'     => '>=12.3.0',
        'taoClientDiagnostic'  => '>=4.0.0',
        'taoTestRunnerPlugins' => '>=2.0.2',
        'taoResultExports' => '>=0.8.0',
        'taoLti'          => '>=4.1.0',
        'taoSystemStatus' => '>=0.13.0',
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
            SetupEventsCapture::class,
            [ToggleGuestAccess::class, ['off']],
            SetResourceSelector::class,
            addAssignmentSettings::class,
            SetClientRouterConfig::class,
            EnableGdprMessage::class,
            RegisterInvalsiTestCategoryPresetProvider::class,
            oat\taoInvalsi\scripts\install\RegisterBookletsExport::class,
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
