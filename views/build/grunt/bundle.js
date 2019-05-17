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
 * configure the extension bundles
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
module.exports = function(grunt) {
    'use strict';

    grunt.config.merge({
        bundle : {
            taoinvalsi : {
                options : {
                    extension : 'taoInvalsi',
                    outputDir : 'loader',
                    dependencies : ['taoQtiTest', 'taoTests'],
                    bundles : [
                        {
                            name : 'testPlugins',
                            include: ['taoInvalsi/runner/plugins/**/*'],
                            dependencies : []
                        },
                        {
                            name : 'taoInvalsi',
                            default : true
                        }
                    ]
                }
            }
        }
    });

    // bundle task
    grunt.registerTask('taoinvalsibundle', ['bundle:taoinvalsi']);
};
