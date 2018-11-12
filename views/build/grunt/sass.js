module.exports = function(grunt) {
    'use strict';

    var root    = grunt.option('root') + '/taoInvalsi/views/';
    var pluginDir = root + 'js/runner/plugins/';

    grunt.config.merge({
        sass : {
            taoinvalsi: {
                files : [
                    { dest : root + 'css/themes/platform/taoInvalsiDefaultTheme/theme.css', src : root + 'scss/themes/platform/taoInvalsiDefaultTheme/theme.scss' },
                    { dest : root + 'css/themes/platform/taoInvalsiTestRunnerTheme/theme.css', src : root + 'scss/themes/platform/taoInvalsiTestRunnerTheme/theme.scss' },
                    // { dest : root + 'css/test-runner.css', src : root + 'scss/test-runner.scss' },
                    // { dest : root + 'css/new-test-runner.css', src :  root + 'scss/new-test-runner.scss'}
                ]
            },
        },
        watch : {
            taoinvalsisass : {
                files : [root + 'scss/**/*.scss', pluginDir + '**/*.scss'],
                tasks : ['sass:taoinvalsi'],
                options : {
                    debounceDelay : 1000
                }
            }
        },
        notify : {
            taoinvalsisass : {
                options: {
                    title: 'Grunt SASS',
                    message: 'SASS files compiled to CSS'
                }
            }
        }
    });

    //register an alias for main build
    grunt.registerTask('taoinvalsisass', ['sass:taoinvalsi']);
};
