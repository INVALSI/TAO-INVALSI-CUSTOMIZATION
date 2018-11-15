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

    if (!$(document.body).hasClass('delivery-scope')) {
        return false;
    }

    // 1. initialise dropdown component
    function init() {
        // prepare container element, sibling to existing menu
        $('.settings-menu').addClass('hidden');
        $('<div id="user-menu" class="hidden"></div>').insertAfter($('.settings-menu').first());

        populate();
    }

    // 2. populate dropdown with data extracted from original menu structure
    // This part is quite specific to both the original menu structure and the Invalsi menu design
    function populate() {
        var data;
        var ddMenu;
        var $aboutHtml;
        var $oldMenu = $('.settings-menu');
        var $ul = $oldMenu.children('ul').first();
        // make some alterations in-place:
        $ul.children(':nth-child(1)').find('span:nth-child(2)').removeClass('hidden');
        $ul.children(':nth-child(1)').addClass('sep-before');
        $ul.children(':nth-child(2)').removeClass('sep-before');
        $ul.children(':nth-child(3)').removeClass('sep-before');

        if ($ul.length) {
            data = {
                userHtml:  $ul.children(':nth-child(2)').html(),    // discarded wrapping <li>, for header
                homeHtml: $ul.children(':nth-child(1)')[0].outerHTML,
                logoutHtml: $ul.children(':nth-child(3)')[0].outerHTML,
                exitHtml: $ul.children(':nth-child(4)')[0].outerHTML
            };
            // build brand new list item:
            $aboutHtml = $('<li>').addClass('about-trigger sep-before');
            $aboutHtml.append(
                $('<span>').addClass('a clickable').attr('tabindex', '0')
                .append($('<span>').addClass('icon icon-info'))
                .append($('<span>').addClass('text').html(__('About')))
            );

            // initialise dropdown:
            ddMenu = dropdown({
                id: 'user-menu',
                renderTo: '#user-menu',
                replace: true,
                activatedBy: 'hover'
            });

            ddMenu.setHeader(data.userHtml);
            ddMenu.clearItems();
            ddMenu.addItem($aboutHtml[0].outerHTML);
            ddMenu.addItem(data.homeHtml);
            ddMenu.addItem(data.logoutHtml);
            ddMenu.addItem(data.exitHtml);
            ddMenu.render();

            $oldMenu.remove();
            $('#user-menu').removeClass('hidden');
        }
        prepDialog();
    }

    // 3. wire up About link to About dialog:
    function prepDialog() {

        var $dialogContent = $('<div>')
        .addClass('platform-info')
        .html(
            $('#info-dialog-content').html()
        );

        var $aboutDialog = dialog({
            content: $dialogContent[0].outerHTML,  // content still needs wrapper for styles
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