const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public').mergeManifest();

mix.js(__dirname + '/Resources/assets/js/app.js', 'modules/messages/js/app.js')
    .sass( __dirname + '/Resources/assets/sass/app.scss', 'modules/messages/css/app.css');

if (mix.inProduction()) {
    mix.version();
}