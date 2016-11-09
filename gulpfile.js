process.env.DISABLE_NOTIFIER = true;

const elixir = require('laravel-elixir');
require('laravel-browser-sync');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.sass('app.scss');
    mix.webpack('app.js');
    mix.browserSync({
        proxy: 'slackleaderboard.local',
        'js': [
            'public/**/*.js',
        ],
        'css': [
            'public/**/*.css',
        ],
        'views': [
            'resources/views/**/*'
        ]
    });
});
