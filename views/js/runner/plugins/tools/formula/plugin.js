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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
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

    var defaults = {
        documentUrl: ''
    };

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
            var config          = _.defaults(pluginsConfig.formula || {}, defaults);
            var areaBroker      = testRunner.getAreaBroker();

            /**
             * Checks if the plugin is currently available
             * activate with category: x-tao-option-formula
             * @returns {Boolean}
             */
            function isEnabled() {
                var context = testRunner.getTestContext();
                return context && context.options && !!context.options.formula;
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
                            document : config.documentUrl
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
