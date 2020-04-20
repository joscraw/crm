var Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');
var dotenv = require('dotenv');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')

    .addEntry('layout', './assets/js/layout.js')
    .addEntry('custom_object_settings', './assets/js/custom_object_settings.js')
    .addEntry('property_settings', './assets/js/property_settings.js')
    .addEntry('record_list', './assets/js/record_list.js')
    .addEntry('report_settings', './assets/js/report_settings.js')
    .addEntry('list_settings', './assets/js/list_settings.js')
    .addEntry('user_settings', './assets/js/user_settings.js')
    .addEntry('security', './assets/js/security.js')
    .addEntry('form_settings', './assets/js/form_settings.js')
    .addEntry('workflows', './assets/js/workflows.js')
    .addEntry('conversations', './assets/js/conversations.js')
    .addEntry('googleRedirect', './assets/js/googleRedirect.js')

    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

    /*.createSharedEntry('vendor', 'babel-polyfill')*/

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables Sass/SCSS support
    .enableSassLoader()

    // enable post CSS loader
    .enablePostCssLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    .enableReactPreset()

    // todo ask travis if this babel stuff here is actually needed.
    .configureBabel(function (babelConfig) {
        babelConfig.plugins = [
            "@babel/plugin-proposal-object-rest-spread","@babel/plugin-proposal-class-properties",
            "@babel/plugin-transform-runtime"
        ]
    })

    // copies to {output}/static
    .addPlugin(new CopyWebpackPlugin([
        {from: 'assets/static', to: 'static'},
        {from: './node_modules/ckeditor/', to: 'ckeditor/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: false},
        {from: './node_modules/ckeditor/adapters', to: 'ckeditor/adapters/[path][name].[ext]'},
        {from: './node_modules/ckeditor/lang', to: 'ckeditor/lang/[path][name].[ext]'},
        {from: './node_modules/ckeditor/plugins', to: 'ckeditor/plugins/[path][name].[ext]'},
        {from: './node_modules/ckeditor/skins', to: 'ckeditor/skins/[path][name].[ext]'}

        ]))

    .configureDefinePlugin(options => {
        const env = dotenv.config();

        if (env.error) {
            throw env.error;
        }

        options['process.env'].AUTH0_CLIENT_ID = JSON.stringify(env.parsed.AUTH0_CLIENT_ID);
        options['process.env'].AUTH0_DOMAIN = JSON.stringify(env.parsed.AUTH0_DOMAIN);
        options['process.env'].AUTH0_CONNECTION = JSON.stringify(env.parsed.AUTH0_CONNECTION);
        options['process.env'].AUTH0_AUDIENCE = JSON.stringify(env.parsed.AUTH0_AUDIENCE);
        options['process.env'].AUTH0_RETURN_TO = JSON.stringify(env.parsed.AUTH0_RETURN_TO);
        options['process.env'].AUTH0_REDIRECT_URI = JSON.stringify(env.parsed.AUTH0_REDIRECT_URI);

    })

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')

;

const config = Encore.getWebpackConfig();
config.watchOptions = {
    poll: true,
};

module.exports = config;
