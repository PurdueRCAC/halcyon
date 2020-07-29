const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../../public').mergeManifest();

mix.js(__dirname + '/Admin/assets/js/html5.js', 'themes/admin/js/html5.js');
mix.js(__dirname + '/Admin/assets/js/index.js', 'themes/admin/js/index.js');
    //.sass( __dirname + '/Resources/assets/sass/app.scss', 'modules/news/css/site.css');

if (mix.inProduction()) {
    mix.version();
}