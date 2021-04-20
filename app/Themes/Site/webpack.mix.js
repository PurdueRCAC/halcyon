const mix = require('laravel-mix');
const WebpackShellPlugin = require('webpack-shell-plugin');
const themeInfo = require('./theme.json');

/**
 * Compile SCSS
 */
mix.sass('assets/scss/site.scss', 'assets/css').options({
    processCssUrls: false
});

/**
 * Publishing the assets
 */
mix.webpackConfig({
    plugins: [
        new WebpackShellPlugin({ onBuildEnd: ['php ../../../artisan theme:publish ' + themeInfo.name] })
    ]
});

if (mix.inProduction()) {
    mix.version();
}
