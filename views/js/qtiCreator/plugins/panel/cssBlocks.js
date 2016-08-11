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
define([
    'jquery',
    'lodash',
    'i18n',
    'core/plugin',
    'helpers',
    'taoQtiItem/qtiCreator/helper/panel',
    'taoInvalsi/qtiCreator/plugins/panel/cssConfig',
    'tpl!taoInvalsi/qtiCreator/plugins/panel/tpl/css-blocks'
], function($, _, __, pluginFactory, helpers, panel, cssConfig, blockTpl) {
    'use strict';

    var _ns = '.cssBlocks';

    var _config = cssConfig.getConfig();


    return pluginFactory({
        name : 'cssBlocks',

        /**
         * Initialize the plugin (called during runner's init)
         */
        init : function init(){
            var self = this;

            // var itemCreator = this.getHost();
            var item = this.getHost().getItem();


            // Happens in dev when all code is installed but the theme is not compiled yet.
            // No big deal and no reason to let the code fail
            if(_config.defaultTheme) {
                require(['css!' + _config.defaultTheme]);
            }
            else {
                console.info('Theme CSS not yet available');
            }

            // create the element

            this.$element = $(blockTpl(_config));

            this.$element.find('input').on('change' + _ns, function() {
                self.$itemBody.toggleClass(this.name, this.checked);
                item.toggleClass(this.name, this.checked);
            }).each(function(){
                this.checked = item.hasClass(this.name);
            });
        },

        render : function render(){
            this.$itemBody = this.getAreaBroker().getContainer().find('.qti-itemBody');
            this.getAreaBroker().getItemStylePanelArea().prepend(this.$element);
            panel.initSidebarAccordion(this.$element);
            this.$element.find('._accordion').trigger('click');
            this.$element.show();
        }
    });
});
