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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'ui/dropdown',
    'ui/dialog',
    'tpl!taoInvalsi/dropdown/list-header',
    'tpl!taoInvalsi/dropdown/list-item',
    'tpl!taoInvalsi/dropdown/dialog-content'
], function ($, _, __, module, dropdown, dialog, listHeaderTpl, listItemTpl, dialogTpl){
    'use strict';

    var userMenuController = {

        /**
         *  Add dropdown container to page, sibling to existing menu
         *  We will switch between 2 menus (classic -> dropdown), carrying some content across...
         */
        start: function start() {
            var $oldMenu = $('.settings-menu').first();
            var newMenuTarget = '#user-menu';
            var dropdownMenu;
            var menuData;

            $oldMenu.addClass('hidden');
            // add placeholder:
            $('<div>').attr('id', 'user-menu').insertAfter($oldMenu);

            menuData = this.extractData($oldMenu);
            if (_.size(menuData)) {
                dropdownMenu = this.createDropdown(newMenuTarget, menuData);
                this.addDialogBehaviour(dropdownMenu);

                // switch to display new menu if all ok:
                if (dropdownMenu.is('rendered')) {
                    $oldMenu.remove();
                    $(newMenuTarget).removeClass('hidden');
                }
                else {
                    $oldMenu.removeClass('hidden');
                }
            }
        },

        /**
         * Extract HTML list items from old menu
         *
         * @param {jQueryElement} $menu
         * @returns {Object}
         */
        extractData: function extractData($menu) {
            var data = {};
            var $ul = $menu.children('ul').first();
            var $homeLi = $ul.children('[data-control="home"]'),
                $logoutLi = $ul.children('[data-control="logout"]'),
                $exitLi = $ul.children('[data-control="exit"]');

            if ($ul.length) {
                // make some alterations in-place to the items we need to copy:
                $ul.children().removeClass('sep-before');
                $homeLi.find('.text').removeClass('hidden');

                data = {
                    home: {
                        content: $homeLi.html(),
                        cls: 'sep-before',
                        id: 'home'
                    },
                    logout: {
                        content: $logoutLi.html(),
                        cls: $logoutLi.attr('class'),
                        id: 'logout'
                    },
                    exit: {
                        content: $exitLi.html(),
                        cls: $exitLi.attr('class'),
                        id: 'exit'
                    },
                    about: {
                        content: listItemTpl({
                            icon: 'info',
                            text: __('About')
                        }),
                        cls: 'sep-before',
                        id: 'about'
                    }
                };
                // build brand new list header:
                data.userHtml = listHeaderTpl({
                    cls: 'a',
                    icon: 'test-taker',
                    text: $ul.find('.icon-test-taker').next('.text').text()
                });
            }
            return data;
        },


        /**
         *  Create & populate dropdown with data extracted from original menu structure
         *
         * @param {String} target
         * @param {Object} data
         * @returns {dropdown component}
         */
        createDropdown: function createDropdown(target, data) {
            var ddMenu = dropdown({
                renderTo: target,
                replace: true,
                activatedBy: 'hover'
            });

            ddMenu.setHeader(data.userHtml);
            ddMenu.addItem(data.about);
            // Handle external config options passed as route params:
            if (module.config().page === 'testRunner' || _.isEmpty(module.config())) {
                ddMenu.addItem(data.home);
            }
            if (module.config().page === 'delivery' || _.isEmpty(module.config())) {
                ddMenu.addItem(data.logout);
                ddMenu.addItem(data.exit);
            }

            return ddMenu;
        },


        /**
         * Wire up About link to About dialog
         *
         * @param {dropdown component} ddMenu
         */
        addDialogBehaviour: function addDialogBehaviour(ddMenu) {
            var aboutDialog = dialog({
                content: dialogTpl({
                    year: new Date().getFullYear()
                }),
                buttons: 'ok',
                autoRender: false,
                autoDestroy: false
            });

            ddMenu.on('item-click-about', function() {
                aboutDialog.show();
            });
        }

    };

    return userMenuController;
});