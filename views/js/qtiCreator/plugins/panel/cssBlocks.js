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
define([
    'jquery',
    'lodash',
    'i18n',
    'core/plugin',
    'helpers',
    'taoQtiItem/qtiCreator/helper/panel',
    'taoInvalsi/qtiCreator/plugins/panel/cssConfig',
    'tpl!taoInvalsi/qtiCreator/plugins/panel/tpl/css-blocks'
], function ($, _, __, pluginFactory, helpers, panel, cssConfig, blockTpl) {
    'use strict';

    var _ns = '.cssBlocks';

    var _config = cssConfig.getConfig();

    /**
     * Toggle labels for choices as table interaction
     */
    var updateChoicesAsTable = function updateChoicesAsTable() {
        var isChoiceAsTableChecked = $('input[name=__custom__choice-interaction-table]').prop('checked');
        var $choiceInteractions = $('div.qti-interaction.qti-blockInteraction.qti-choiceInteraction');
        if ($choiceInteractions.length) {
            $choiceInteractions.first().toggleClass('first-choice-interaction', isChoiceAsTableChecked);
        }
    };

    return pluginFactory({
        name: 'cssBlocks',

        /**
         * Initialize the plugin (called during runner's init)
         */
        init: function init() {
            var self = this;
            var item = this.getHost().getItem();

            // Happens in dev when all code is installed but the theme is not compiled yet.
            // No big deal and no reason to let the code fail
            if (_config.defaultTheme) {
                require(['css!' + _config.defaultTheme]);
            }

            // create the element
            this.$element = $(blockTpl(_config));

            this.$element.find('input').on('change' + _ns, function () {
                self.$itemBody.toggleClass(this.name, this.checked);
                item.toggleClass(this.name, this.checked);

                updateChoicesAsTable();
            }).each(function () {
                this.checked = item.hasClass(this.name);
            });
        },

        render: function render() {
            this.$itemBody = this.getAreaBroker().getContainer().find('.qti-itemBody');
            this.getAreaBroker().getItemStylePanelArea().prepend(this.$element);
            panel.initSidebarAccordion(this.$element);
            this.$element.find('._accordion').trigger('click');
            this.$element.show();

            $(document).on('deleted.qti-widget', updateChoicesAsTable);

            updateChoicesAsTable();
        }
    });
});
