/*
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  Copyright (c) 2016 - 2019 (original work) Open Assessment Technologies S.A.
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
