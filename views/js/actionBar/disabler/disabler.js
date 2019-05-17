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
