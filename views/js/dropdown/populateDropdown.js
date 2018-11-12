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

    var ddMenu;

    if (!$(document.body).hasClass('delivery-scope')) {
        return false;
    }

    // 1. initialise dropdown component in specified div
    function init() {
        ddMenu = dropdown({
            id: 'user-menu',
            headerItem: {icon: 'test-taker', text: 'Username'},
            innerItems: [{html: "hello"}],
            renderTo: '#user-menu-placeholder',
            replace: true,
            activatedBy: 'hover'
        });
        populate();
    }

    // 2. populate dropdown with data extracted from original menu structure
    // This is quite specific to the Invalsi menu design
    function populate() {
        var data;
        var $menu = $('.settings-menu');
        var $ul = $menu.children('ul').first();
        $ul.children(':nth-child(1)').find('span:nth-child(2)').removeClass('hidden');
        $ul.children(':nth-child(1)').addClass('sep-before');
        $ul.children(':nth-child(2)').removeClass('sep-before');
        $ul.children(':nth-child(3)').removeClass('sep-before');

        if ($ul.length) {
            data = {
                userHtml:  $ul.children(':nth-child(2)').html(),    // discard wrapping <li> for header
                homeHtml: $ul.children(':nth-child(1)')[0].outerHTML,
                logoutHtml: $ul.children(':nth-child(3)')[0].outerHTML,
                exitHtml: $ul.children(':nth-child(4)')[0].outerHTML,
                aboutData: {text: __('About'), icon: 'info', cls: 'about-trigger sep-before'}
            };
            ddMenu.setHtmlHeader(data.userHtml);
            ddMenu.clearItems();
            ddMenu.addItem(data.aboutData);
            ddMenu.addHtmlItem(data.homeHtml);
            ddMenu.addHtmlItem(data.logoutHtml);
            ddMenu.addHtmlItem(data.exitHtml);
            ddMenu.render();
            $menu.remove();
        }
        prepDialog();
    }

    // 3. wire up About link to dialog:
    function prepDialog() {

        var $aboutDialog = dialog({
            content: $('#info-dialog-content').html(),  // content still needs wrapper for styles
            buttons: 'ok',
            autoRender: false,
            autoDestroy: false
        });

        $('.about-trigger').on('click', function() {
            $aboutDialog.render();
        });

        $('#info-dialog-content').remove();

        /* testing:
        $(document).on('keypress', function(e) {
            console.log(e.key);
            switch (e.key) {
                case 'n':
                    ddMenu.addItem({text: 'another', icon: 'folder'});
                    break;
                case 'h':
                    ddMenu.addHtmlItem('<li><marquee>yeah</marquee></li>');
                    break;
                case 'r':
                    ddMenu.removeItem(0);
                    break;
                case 'c':
                    ddMenu.clearItems();
                    break;
            }
        });
        */
    }

    return {
        init: init
    };
});