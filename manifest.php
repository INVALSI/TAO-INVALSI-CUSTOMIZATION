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

return array(
    'name' => 'taoInvalsi',
    'label' => 'TAO INVALSI',
    'description' => 'Custom code for Invalsi',
    'license' => 'GPL-2.0',
    'version' => '3.0.0',
	'author' => 'Open Assessment Technologies SA',
	'requires' => array(
        'taoCe' => '>=1.2.0',
        'tao' => '>=7.67.0',
        'taoMediaManager' => '*',
        'taoQtiTest' => '>=5.43.0',
        'taoQtiItem' => '>=6.2.0',
        'taoTests' => '>=3.5.0',
        'pciSamples' => '>=1.3.0',
        'taoMonitoring' => '>=0.2.0'
    ),
	'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoInvalsiManager',
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoInvalsiManager', array('ext'=>'taoInvalsi'))
    ),
    'install' => array(
        'php' => array(
            __DIR__.'/scripts/install/setThemeConfig.php',
            __DIR__ . '/scripts/install/addHook.php',
            'oat\\taoInvalsi\\scripts\\install\\SetQtiCreatorConfig',
            'oat\\taoInvalsi\\scripts\\install\\SetNewTestRunner',
            'oat\\taoInvalsi\\scripts\\install\\SetNewTestRunnerConfig',
            'oat\\taoInvalsi\\scripts\\install\\RegisterTestRunnerPlugins',
            'oat\\taoInvalsi\\scripts\\install\\RegisterTestRunnerFeatures',
            'oat\\taoInvalsi\\scripts\\install\\SetPasswordPolicy',
            'oat\\taoMonitoring\\scripts\\install\\RegisterAwsFileSystem',
        )
    ),
    'update' => 'oat\\taoInvalsi\\scripts\\update\\Updater',
    'uninstall' => array(
    ),
	'constants' => array(
	    # views directory
	    "DIR_VIEWS" => dirname(__FILE__).DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR,

            #BASE URL (usually the domain root)
            'BASE_URL' => ROOT_URL.'taoInvalsi/',

	    #BASE WWW required by JS
	    'BASE_WWW' => ROOT_URL.'taoInvalsi/views/'
	),
    'extra' => array(
        'structures' => dirname(__FILE__).DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'structures.xml',
    )
);
