const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/bundles/markocupiccontaocustomglobaloperation')
    .setManifestKeyPrefix('')

    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps()
    .enableVersioning()

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .enablePostCssLoader()
    // Preprocessing SCSS to CSS
    .enableSassLoader()
    .enablePostCssLoader()
    .addStyleEntry('css/backend/styles', './assets/backend/styles/scss/styles.scss')
;

module.exports = Encore.getWebpackConfig();
