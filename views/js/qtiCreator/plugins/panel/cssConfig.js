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
 *
 * @author dieter <dieter@taotesting.com>
 */
define(['lodash', 'i18n', 'ui/themes', 'ui/themeLoader'], function (_, __, themeHandler, themeLoader) {
    'use strict';

    /**
     * Configuration
     *
     * Each of these elements will be one checkbox in the style editor, edit this
     * part according to your own needs. Note that class names will automatically
     * be prefixed by '__custom__' to avoid conflicts. For the translation of the
     * labels it is essential to call __() as done in the example below. Don't be
     * tempted to do the translation in the loop below instead as this will prevent
     * the translation scanner from finding these text strings.
     *
     * Element format: {  className: 'the-class-name',  label: __('The Label')   }
     *
     * @type {*[]}
     */
    var blocks = [
        {
            className: 'center-picture',
            label: 'Center Picture'
        },
        {
            className: 'bullets-to-checkbox-left',
            label: 'Bullets to checkbox left'
        },
        {
            className: 'choice-interaction-table',
            label: 'Choices as table'
        },
        {
            className: 'choice-interaction-tiles',
            label: 'Choices as tiles'
        },
        {
            className: 'text-entry-fixed-size',
            label: 'Text entry fixed size'
        },
        {
            className: 'match-interaction-nowrap',
            label: 'Match interaction no line break'
        },
        {
            className: 'bordered-item',
            label: 'Bordered item'
        },
        {
            className: 'bordered-columns',
            label: 'Bordered columns'
        },
        {
            className: 'vertical-column-border',
            label: 'Vertical column border'
        }
    ];

    /**
     * Initialize the configuration object
     *
     * @type {{blocks, defaultTheme}}
     */
    var cssConfig = {
        blocks: (function () {
            var i = blocks.length;
            while (i--) {
                blocks[i].className = '__custom__' + blocks[i].className;
            }
            return blocks;
        })(),
        defaultTheme: (function () {
            var themeObj = themeHandler.get('items') || {};
            var themeId = themeLoader(themeObj).getActiveTheme();
            var themePath = '';
            _(themeObj.available).forEach(function (data) {
                if (data.id === themeId) {
                    themePath = data.path;
                    return false;
                }
            });
            return themePath;
        })()
    };

    /**
     * @exports
     */
    return {
        getConfig: function () {
            return cssConfig;
        }
    };
});
