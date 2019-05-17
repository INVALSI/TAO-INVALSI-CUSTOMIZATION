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
 * Test Runner Tool Plugin : Add a CSS selector to items that are formatted as `__custom__choice-interaction-table`
 *
 * @author Dieter Raber <dieter@taotesting.com>
 */
define([
    'taoTests/runner/plugin'
], function (pluginFactory) {
    'use strict';

    /**
     * Returns the configured plugin
     */
    return pluginFactory({

        name: 'tableChoiceHelper',

        /**
         * Initialize the plugin (called during runner's init)
         */
        init: function init() {            

            var self = this;
            var testRunner = this.getTestRunner();

            //update plugin state based on changes
            testRunner
                .after('renderitem', function () {
                    // Note: The first interaction has different styles. Originally this had been addressed with :first-of-type, 
                    // this became impossible after the HTML structure had been changed
                    self.getAreaBroker().getContentArea().find('.__custom__choice-interaction-table .qti-choiceInteraction').first().addClass('first-choice-interaction');
                });
        }
    });
});
