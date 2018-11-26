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
 */

namespace oat\taoInvalsi\scripts\install;

use common_ext_action_InstallAction as InstallAction;
use common_report_Report as Report;
use oat\taoTests\models\runner\plugins\PluginRegistry;
use oat\taoTests\models\runner\plugins\TestPlugin;

/**
 * Install action that registers the test runner plugins
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class RegisterTestRunnerPlugins extends InstallAction
{

    public static $plugins = [
        'content' => [
            [
                'id' => 'answerCache',
                'name' => 'Answers cache',
                'module' => 'taoInvalsi/runner/plugins/content/answerCache',
                'bundle' => 'taoInvalsi/loader/testPlugins.min',
                'description' => 'Cache the answers to restore them after refresh',
                'category' => 'content',
                'active' => true,
                'tags' => [ ]
            ],
            [
                'id' => 'tableChoiceHelper',
                'name' => 'Tabe Choice Helper',
                'module' => 'taoInvalsi/runner/plugins/content/tableChoiceHelper',
                'bundle' => 'taoInvalsi/loader/testPlugins.min',
                'description' => 'Adds a CSS selector to each first choice interaction of each block',
                'category' => 'content',
                'active' => true,
                'tags' => [ ]
            ],
        ],       
        
        'security' => [
            [
                'id' => 'disableRightClick',
                'name' => 'Disable right click',
                'module' => 'taoQtiTest/runner/plugins/security/disableRightClick',
                'bundle' => 'taoQtiTest/loader/testPlugins.min',
                'description' => 'Disable right click context menu on items',
                'category' => 'security',
                'active' => true,
                'tags' => [ 'core' ]
            ]
        ],
        'tools' => [
            [
                'id' => 'formula',
                'name' => 'Formula PDF',
                'module' => 'taoInvalsi/runner/plugins/tools/formula/plugin',
                'bundle' => 'taoInvalsi/loader/testPlugins.min',
                'description' => 'Display a formula PDF',
                'category' => 'tools',
                'active' => true,
                'tags' => [ ]
            ],
            [
                'id' => 'documentViewer',
                'module' => 'taoQtiTest/runner/plugins/tools/documentViewer/documentViewer',
                'bundle' => 'taoQtiTest/loader/testPlugins.min',
                'name' => 'Document Viewer',
                'description' => 'Display a document as requested by an event',
                'category' => 'tools',
                'active' => true,
                'tags' => [ ]
            ],
            [
                'id' => 'textToSpeech',
                'name' => 'Text to Speech',
                'module' => 'taoTextHelp/runner/plugins/tools/textToSpeech/plugin',
                'bundle' => 'taoTextHelp/loader/testPlugins.min',
                'description' => 'Enables text-to-speech via Texthelp',
                'category' => 'tools',
                'active' => true,
                'tags' => []
            ],
            [
                'id' => 'preventDropToInput',
                'name' => 'Prevent Drop To Input',
                'module' => 'taoTestRunnerPlugins/runner/plugins/tools/preventDropToInput',
                'bundle' => 'taoTestRunnerPlugins/loader/testPlugins.min',
                'description' => 'Prevent images movement to inputs',
                'category' => 'tools',
                'active' => true,
                'tags' => []
            ]
        ]
    ];

    /**
     * Run the install action
     */
    public function __invoke($params)
    {

        $registry = PluginRegistry::getRegistry();
        $count = 0;

        foreach(self::$plugins as $categoryPlugins) {
            foreach($categoryPlugins as $pluginData){
                if( $registry->register(TestPlugin::fromArray($pluginData)) ) {
                    $count++;
                }
            }
        }

        return new Report(Report::TYPE_SUCCESS, $count .  ' plugins registered.');
    }
}
