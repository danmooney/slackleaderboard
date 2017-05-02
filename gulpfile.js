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

elixir.config.css.sass.folder = 'scss';

elixir(function (mix) {
    mix.sass('main.scss'/*, undefined, 'resources/assets/scss'*/);
    mix.webpack('main.js');
    mix.browserSync({
        proxy: 'slackleaderboard.local',
        js: [
            'public/**/*.js'
        ],
        css: [
            'public/**/*.css'
        ],
        views: [
            'resources/views/**/*'
        ],
        open: false // don't open the browser window initially
    });
});
