var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .addEntry('contao-menu-bundle', './assets/js/contao-menu-bundle-init.js')
    .setPublicPath('/public/')
    .disableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
