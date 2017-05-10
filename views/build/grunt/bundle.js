module.exports = function(grunt) {
    'use strict';

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    var out         = 'output';

    var testPlugins = ext.getExtensionSources('taoInvalsi', ['views/js/runner/plugins/**/*.js'], true);

    var paths = {
        'taoTests' : root + '/taoTests/views/js'
    };

    grunt.config.merge({
        requirejs: {
            taoinvalsiplugins : {
                options: {
                    paths : paths,
                    include: testPlugins,
                    exclude : ['json!i18ntr/messages.json'].concat(libs),
                    out: out + "/testPlugins.min.js"
                }
            }
        },

        copy : {
            taoinvalsibundle : {
                files: [
                    { src: [out + '/testPlugins.min.js'],  dest: root + '/taoInvalsi/views/js/loader/testPlugins.min.js' },
                    { src: [out + '/testPlugins.min.js.map'],  dest: root + '/taoInvalsi/views/js/loader/testPlugins.min.js.map' }
                ]
            }
        }
    });

    // bundle task
    grunt.registerTask('taoinvalsibundle', ['clean:bundle', 'requirejs:taoinvalsiplugins', 'copy:taoactbundle']);
};
