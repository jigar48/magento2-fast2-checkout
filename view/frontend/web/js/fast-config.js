define([
    'domReady!',
    'jquery',
], function ($) {
    var serverConfig = null;
    var theConfig = null;

    return function (config) {
        // When we are invoked with the server config,
        // set it in a persistent variable and notify the rest of the application
        if (serverConfig === null && typeof config === 'object' && config !== null) {
            serverConfig = config;
            //$(document).trigger('fast-magento-config-initialized');
        }

        // Return cached config object if called repeatedly
        if (theConfig !== null) {
            return theConfig;
        }

        theConfig = {
            getAppId: function () {
                return serverConfig && serverConfig.appId ? serverConfig.appId : '';
            },
            getBtnTheme: function () {
                return serverConfig && serverConfig.buttonTheme ? serverConfig.buttonTheme : '';
            },
            getClearCartUrl: function () {
                return serverConfig && serverConfig.clearCartUrl ? serverConfig.clearCartUrl : '';
            },
            /**
             * Check to see if Fast is enabled and all cart products are Fast supported products.
             */
            shouldShowFastOnCart: function () {
                var isFastEnabled = serverConfig && serverConfig.fastEnabled && serverConfig.fastEnabled === true;
                var areAllProductsFast = serverConfig && serverConfig.areAllProductsFast && serverConfig.areAllProductsFast === true;

                return isFastEnabled && areAllProductsFast;
            },
            /**
             * Check to see if Fast is enabled and all cart products are Fast supported products.
             */
            shouldShowFastOnPDP: function () {
                var isFastEnabled = serverConfig && serverConfig.fastEnabled && serverConfig.fastEnabled === true;
                var isProductFast = serverConfig && serverConfig.isProductFast && serverConfig.isProductFast === true;

                return isFastEnabled && isProductFast;
            }
        };

        return theConfig;
    };
});