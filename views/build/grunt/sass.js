module.exports = function (grunt) {
    'use strict';

    var sass    = grunt.config('sass') || {};
    var watch   = grunt.config('watch') || {};
    var notify  = grunt.config('notify') || {};
    var root    = grunt.option('root') + '/taoInvalsi/views/';

    // Override include paths
    sass.taoinvalsi = {
        options : {},
        files : {}
    };

    //files goes heres
    sass.taoinvalsi.files[root + 'css/themes/items/theme-invalsi/theme.css'] = root + 'scss/themes/items/theme-invalsi/theme.scss';


    watch.taoinvalsisass = {
        files : [root + 'scss/**/*.scss'],
        tasks : ['sass:taoinvalsi', 'notify:taoinvalsisass'],
        options : {
            debounceDelay : 1000
        }
    };

    notify.taoinvalsisass = {
        options: {
            title: 'Grunt SASS',
            message: 'SASS files compiled to CSS'
        }
    };

    grunt.config('sass', sass);
    grunt.config('watch', watch);
    grunt.config('notify', notify);

    //register an alias for main build
    grunt.registerTask('taoinvalsisass', ['sass:taoinvalsi']);
};
