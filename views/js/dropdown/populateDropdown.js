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
    'ui/dialog'
], function ($, __, dropdown, dialog){
    'use strict';

    // we will switch between 2 menus (classic -> dropdown), carrying some content across...
    var $oldMenu = $('.settings-menu').first();
    var newMenuTarget = '#user-menu';

    // 1. add dropdown container to page, sibling to existing menu
    function init() {
        $oldMenu.addClass('hidden');
        $('<div id="user-menu" class="hidden"></div>').insertAfter($oldMenu);

        extractData($oldMenu);
    }

    // 2. extract HTML list items from old menu
    // This part is quite specific to both the original menu structure and the Invalsi menu design
    function extractData($menu) {
        var data;
        var $ul = $menu.children('ul').first();

        if ($ul.length) {
            // make some alterations in-place:
            $ul.children(':nth-child(1)').find('span:nth-child(2)').removeClass('hidden');
            $ul.children(':nth-child(1)').addClass('sep-before');
            $ul.children(':nth-child(2)').removeClass('sep-before');
            $ul.children(':nth-child(3)').removeClass('sep-before');

            data = {
                userHtml:  $ul.children(':nth-child(2)').html(),    // discarded wrapping <li>, for header
                homeHtml: $ul.children(':nth-child(1)')[0].outerHTML,
                logoutHtml: $ul.children(':nth-child(3)')[0].outerHTML,
                exitHtml: $ul.children(':nth-child(4)')[0].outerHTML
            };
            // build brand new list item:
            data.$aboutHtml = $('<li>').addClass('about-trigger sep-before');
            data.$aboutHtml.append(
                $('<span>').addClass('a clickable').attr('tabindex', '0')
                .append($('<span>').addClass('icon icon-info'))
                .append($('<span>').addClass('text').html(__('About')))
            );

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
        ddMenu.addItem(data.$aboutHtml[0].outerHTML);
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
