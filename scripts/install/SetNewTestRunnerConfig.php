<?php
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

        $config['plugins']['formula'] = [
            'documentUrl' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L13.pdf',
            'documentUrlL10' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L10.pdf',
            'documentUrlL13' => '/taoInvalsi/views/js/runner/plugins/tools/formula/Formulario_L13.pdf'
        ];

        $config['plugins']['calculator'] = array(
            'template' => 'taoInvalsi/runner/plugins/tools/calculator/template.tpl'
        );

        $config['plugins']['connectivity'] = array(
            'indicator' => false
        );

        //*** TextHelp Setup
        // - URI of the config file that should contain the right setup for the customer
        $config['plugins']['textToSpeech']['setupUrl'] = '//taotoolbar.speechstream.net/tao/configInvQA214.js';
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
            'type'        => 'request',
            'extension'   => 'taoQtiTest',
            'controller'  => 'Runner',
            'action'      => 'messages',
            'syncActions' => [],
            'params'      => []
        ];

        $qtiTest->setConfig('testRunner', $config);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Test runner config saved');
    }
}
