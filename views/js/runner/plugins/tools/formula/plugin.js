/*
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

/**
 * Test Runner Tool Plugin : display a formula sheet PDF
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'taoTests/runner/plugin'
], function ($, _, __, pluginFactory) {
    'use strict';

    var categoryPrefix = 'formula';
    var documentUrlPrefix = 'documentUrl';

    /**
     * Returns the configured plugin
     */
    return pluginFactory({

        name: 'formula',

        /**
         * Initialize the plugin (called during runner's init)
         */
        init: function init() {
            var self = this;

            var testRunner      = this.getTestRunner();
            var testData        = testRunner.getTestData() || {};
            var testConfig      = testData.config || {};
            var pluginsConfig   = testConfig.plugins || {};
            var config          = pluginsConfig.formula || {};
            var areaBroker      = testRunner.getAreaBroker();

            var formulaFile;

            /**
             * To be enabled, this plugin needs 2 things:
             * - a category starting with x-tao-option-formulaXXX
             * - a corresponding plugin option starting with documentUrlXXX
             * both 'XXX' strings have to match for the plugin to be enabled
             * @returns {Boolean}
             */
            function isEnabled() {
                formulaFile = getFormulaFile();
                return ! _.isUndefined(formulaFile);
            }

            /**
             * Returns the plugin option corresponding to the Qti category, if any
             * @returns {String}
             */
            function getFormulaFile() {
                var context = testRunner.getTestContext() || {},
                    options = context.options || {},
                    formulaOption;

                _.find(options, function(optionValue, optionName) {
                    if ((optionName.indexOf(categoryPrefix) === 0) && (optionValue === true)) {
                        formulaOption = documentUrlPrefix + optionName.replace(categoryPrefix, '');
                    }
                });

                if (formulaOption) {
                    return config[formulaOption];
                }
            }

            /**
             * Is plugin activated ? if not, then we hide the plugin
             */
            function togglePlugin() {
                if (isEnabled()) {
                    self.show();
                } else {
                    self.hide();
                }
            }

            //build element (detached)
            this.button = areaBroker.getToolbox().createEntry({
                control : 'formula',
                title : __('Formula'),
                icon : 'maths',
                text : __('Formula')
            });

            //attach user events
            this.button.on('click', function (e) {
                e.preventDefault();
                testRunner.trigger('tool-formula');
            });

            //start disabled
            togglePlugin();
            this.disable();

            //update plugin state based on changes
            testRunner
                .on('loaditem', togglePlugin)
                .on('enabletools renderitem', function () {
                    self.enable();
                })
                .on('disabletools unloaditem', function () {
                    self.disable();
                })
                .on('tool-formula', function () {
                    if (isEnabled()) {
                        testRunner.trigger('tool-documentViewer', {
                            label : __('Formula'),
                            document : formulaFile
                        });
                    }
                });
        },

        /**
         * Enable the button
         */
        enable: function enable() {
            this.button.enable();
        },

        /**
         * Disable the button
         */
        disable: function disable() {
            this.button.disable();
        },

        /**
         * Show the button
         */
        show: function show() {
            this.button.show();
        },

        /**
         * Hide the button
         */
        hide: function hide() {
            this.button.hide();
        }
    });
});
