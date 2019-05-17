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
                'name' => 'Table Choice Helper',
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
