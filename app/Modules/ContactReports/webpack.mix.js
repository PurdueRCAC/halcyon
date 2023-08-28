const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../../public');
mix.mergeManifest();

mix.js(__dirname + '/Resources/assets/js/app.js', 'modules/contactreports/js/app.js').vue();
    //.sass( __dirname + '/Resources/assets/sass/app.scss', 'modules/news/css/site.css');

if (mix.inProduction()) {
    mix.version();
}