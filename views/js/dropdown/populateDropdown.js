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
    'i18n',
    'ui/dropdown',
    'ui/dialog',
    'tpl!taoInvalsi/dropdown/list-header',
    'tpl!taoInvalsi/dropdown/list-item',
], function ($, __, dropdown, dialog, listHeaderTpl, listItemTpl){
    'use strict';

    // we will switch between 2 menus (classic -> dropdown), carrying some content across...
    var $oldMenu = $('.settings-menu').first();
    var newMenuTarget = '#user-menu';

    // 1. add dropdown container to page, sibling to existing menu
    function init() {
        $oldMenu.addClass('hidden');

        extractData($oldMenu);
    }

    // 2. extract HTML list items from old menu
    // This part is quite specific to both the original menu structure and the Invalsi menu design
    function extractData($menu) {
        var data;
        var $ul = $menu.children('ul').first();

        if ($ul.length) {
            // make some alterations in-place to the items we need to copy:
            $ul.children().removeClass('sep-before');
            $ul.children('[data-control="home"]').addClass('sep-before');
            $ul.children('[data-control="home"]').find('.text').removeClass('hidden');

            data = {
                homeHtml: $ul.children('[data-control="home"]')[0].outerHTML,
                logoutHtml: $ul.children('[data-control="logout"]')[0].outerHTML,
                exitHtml: $ul.children('[data-control="exit"]')[0].outerHTML
            };
            // build brand new list header:
            data.userHtml = listHeaderTpl({
                cls: 'a',
                icon: 'text-taker',
                text: $ul.find('.icon-test-taker').next('.text').text()
            });
            // build brand new list item:
            data.aboutHtml = listItemTpl({
                cls: 'about-trigger sep-before',
                icon: 'info',
                text: __('About')
            });

            populate(data);
        }
    }

    // 3. populate dropdown with data extracted from original menu structure
    function populate(data) {
        // initialise dropdown:
        var ddMenu = dropdown({
            //id: 'user-menu',
            renderTo: newMenuTarget,
            replace: true,
            activatedBy: 'hover'
        });

        ddMenu.setHeader(data.userHtml);
        ddMenu.clearItems();
        ddMenu.addItem(data.aboutHtml);
        ddMenu.addItem(data.homeHtml);
        ddMenu.addItem(data.logoutHtml);
        ddMenu.addItem(data.exitHtml);

        cleanUp();
        prepDialog();
    }

    // 4. switch to display new menu
    function cleanUp() {
        $oldMenu.remove();
        $(newMenuTarget).removeClass('hidden');
    }

    // 5. wire up About link to About dialog
    function prepDialog() {
        var $dialogContent = $('<div>')
        .addClass('platform-info')
        .html(
            $('#info-dialog-content').html()
        );

        // init dialog
        var $aboutDialog = dialog({
            content: $dialogContent[0].outerHTML,  // keep its wrapper, for inner styling
            buttons: 'ok',
            autoRender: false,
            autoDestroy: false
        });

        $('.about-trigger').on('click', function() {
            $aboutDialog.render();
        });
    }

    return {
        init: init
    };
});
