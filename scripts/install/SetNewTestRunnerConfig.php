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

use common_ext_ExtensionsManager;

class SetNewTestRunnerConfig extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $invalsiConfig = array(
            'check-informational' => true,
            'test-taker-review' => true,
            'test-taker-review-region' => 'left',
            'test-taker-review-force-title' => true,
            'test-taker-review-item-title' => 'Domanda %d',
            'test-taker-review-force-informational-title' => true,
            'test-taker-review-informational-item-title' => false,
            'test-taker-review-scope' => 'testPart',
            'test-taker-review-prevents-unseen' => true,
            'test-taker-review-can-collapse' => true,
            'force-branchrules' => true,
            'path-tracking' => true,
            'always-allow-jumps' => true,
            'keep-timer-up-to-timeout' => true,
            'enable-allow-skipping' => true,
            'enable-validate-responses' => false,
            'item-cache-size' => 3
        );

        $qtiTest = $this->getServiceManager()->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoQtiTest');
        $config = $qtiTest->getConfig('testRunner');
        $config = array_merge($config, $invalsiConfig);

        unset($config['plugins']['formula']['documentUrlA02_A03_A04']);

        $config['plugins']['formula'] = [
            'documentUrlL10' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L10.pdf',
            'documentUrlL13' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L13.pdf',
            'documentUrlL13_SLO' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L13_SLO.pdf',
            'documentUrlL10_SLO' => 'Formulario_G10_SLO',//this one and the next one - are just the placeholders for two new
            'documentUrlL10_GER' => 'Formulario_G10_GER', //files, to be filled later, when they'd provided by customer
            'documentUrlA71' => '/taoInvalsi/views/js/runner/plugins/tools/formula/formulaA71.pdf', //package for activity 71 Jan2020
            'documentUrlA02A03A04SRD' => '/taoInvalsi/views/js/runner/plugins/tools/formula/formulaA020304SRD.pdf', //package for activity 020304SRD Jan2020
            'documentUrlA141516' => '/taoInvalsi/views/js/runner/plugins/tools/formula/formulaA141516.pdf', //package for activity 141516 Jan2020
            'documentUrlA02_A03_A04' => '/taoInvalsi/views/js/runner/plugins/tools/formula/formulaA020304.pdf', //package for activity 020304 Jan2020
            'documentUrlA02A03A04' => '/taoInvalsi/views/js/runner/plugins/tools/formula/formulaA020304.pdf',
            'documentUrlA080910' => '/taoInvalsi/views/js/runner/plugins/tools/formula/formulaA080910.pdf',
            'documentUrlA09' => '/taoInvalsi/views/js/runner/plugins/tools/formula/formulaA09.pdf',
        ];

        $config['plugins']['calculator'] = array(
            'template' => 'taoInvalsi/runner/plugins/tools/calculator/template.tpl'
        );

        $config['plugins']['connectivity'] = array(
            'indicator' => false
        );

        // default button for confirm dialogs
        $config['plugins']['dialog']['confirm']['focus'] = 'cancel';

        //*** TextHelp Setup
        // - URI of the config file that should contain the right setup for the customer
        $config['plugins']['textToSpeech']['setupUrl'] = '//configuration.speechstream.net/oat/inv/v216/config.js';
        // - override math expressions with an alternative text
        $config['plugins']['textToSpeech']['overrideMathExpr'] = "Guarda la formula";
        // - override the default language crosswalk
        $config['plugins']['textToSpeech']['languagesCrosswalk'] = [
            // "yy" => "xx-XX"
        ];
        // - rely on TextHelp to detect the language based on the content
        $config['plugins']['textToSpeech']['allowTextHelpLangCheck'] = false;
        // - take care of QTI lang attribute
        $config['plugins']['textToSpeech']['allowQtiLangCheck'] = true;
        // - setup the default language for the platform
        $config['plugins']['textToSpeech']['forceLanguage'] = true;
        $config['plugins']['textToSpeech']['voiceLanguage'] = 'it-IT'; // language code
        $config['plugins']['textToSpeech']['voiceGender'] = 'female';  // voice gender: 'female' or 'male'
        $config['plugins']['textToSpeech']['voiceIndex'] = 0;          // when several voices are available for the same gender (cf. languages.json)
        //*** end TextHelp Setup

        $config['bootstrap']['communication'] = [
            'enabled'     => true,
            'type'        => 'poll',
            'extension'   => 'taoQtiTest',
            'controller'  => 'Runner',
            'action'      => 'messages',
            'syncActions' => [],
            'params'      => [
                'interval' => 300, //5 minutes
                'timeout' => 30,
            ]
        ];

        $qtiTest->setConfig('testRunner', $config);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Test runner config saved');
    }
}
