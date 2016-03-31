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
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'lodash',
    'taoQtiTest/testRunner/actionBar/button',
    'tpl!taoInvalsi/actionBar/disabler/button'
], function (_, button, buttonTpl) {
    'use strict';

    /**
     * A list of event name related to the right click
     * @type {Array}
     */
    var rightClickEvents = ['contextmenu', 'mousedown', 'mouseup'];

    /**
     * Disables actions based on the right click
     * @param event
     */
    function disableRightClick(event) {
        if (2 === event.button) {
            event.preventDefault();
        }
    }

    /**
     * Installs the events handler to disable right click
     * @param target
     */
    function installRightClickDisabler(target) {
        if (target) {
            _.forEach(rightClickEvents, function(eventName) {
                target.addEventListener(eventName, disableRightClick, true);
            });
        }
    }

    /**
     * Removes the events handler to disable right click
     * @param target
     */
    function uninstallRightClickDisabler(target) {
        if (target) {
            _.forEach(rightClickEvents, function(eventName) {
                target.removeEventListener(eventName, disableRightClick, true);
            });
        }
    }

    /**
     * Builds a dummy QTI tool which will never be displayed.
     * We use a QTI Tool only because this is the only way to customize the test runner without touching it
     */
    return button({
        /**
         * Additional setup onto the button config set
         * @private
         */
        setup : function setup() {
            uninstallRightClickDisabler(window.document);
            installRightClickDisabler(window.document);

            if (window.parent) {
                uninstallRightClickDisabler(window.parent.document);
                installRightClickDisabler(window.parent.document);
            }
        },

        /**
         * Install disabler on the item frame
         */
        itemLoaded: function itemLoaded() {
            var itemContext = this.getItemContext();
            var itemWindow = itemContext && itemContext.itemContainerWindow;
            if (itemWindow) {
                uninstallRightClickDisabler(itemWindow.document);
                installRightClickDisabler(itemWindow.document);
            }
        },

        /**
         * Disables the button
         * @param {String} [id] - the button id in case of groups
         * @returns {button}
         */
        disable : function disable(id) {
            return this;
        },

        /**
         * Enables the button
         * @param {String} [id] - the button id in case of groups
         * @returns {button}
         */
        enable : function enable(id) {
            return this;
        },

        /**
         * Hide a button
         * @param {String} [id] - the button id in case of groups
         * @returns {button} chains
         */
        hide : function hide(id) {
            return this;
        },

        /**
         * Show a button
         * @param {String} [id] - the button id in case of groups
         * @returns {button} chains
         */
        show : function show(id) {
            return this;
        },

        /**
         * Gets the template renderer
         * @returns {jQuery}
         */
        getTemplate : function getTemplate() {
            return buttonTpl;
        }
    });
});
