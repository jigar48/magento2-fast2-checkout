define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'fastConfig',
    'clearCart'
], function (
    Component,
    customerData,
    $,
    ko,
    _,
    fastConfigFactory,
    fastCartCleanup
) {
    'use strict';

    var fastConfig = fastConfigFactory();

    return Component.extend({
        observableProperties: [
            'items'
        ],
        initialize: function () { 
            var self = this, 
                minicart = $('[data-block="minicart"]');
            this._super();
            self.cartId = ko.observable('');
            self.fastAppId = ko.observable(fastConfig.getAppId());
            self.shouldShowFastButton = ko.observable(fastConfig.shouldShowFastOnCart());
            self.fastDark = ko.observable(fastConfig.getBtnTheme() === 'dark');

            function ajaxCall(callback){
                $.ajax({
                    url: '/fast/config/fast',
                    type: 'GET',
                    dataType: 'json'
                }).done(function(data){
                    self.cartId(data.cartId);
                    self.fastAppId(data.appId);
                    self.fastDark(data.theme === 'dark');
                    self.shouldShowFastButton(data.areAllProductsFast);
                    if(typeof callback === 'function'){
                        callback(data);
                    }
                }).fail(function(data){
                    if(typeof callback === 'function'){
                        callback(null);
                    }
                });
            };
            if((!self.fastAppId() || !self.cartId()) 
                && customerData.get('cart')().items && customerData.get('cart')().items.length > 0){
                //initial cart id lookup on page load
                ajaxCall();
            }
            
            customerData.get('cart').subscribe(
                function (cartData) {
                    //we also subscribe to cart updates to ensure
                    //cart id is up to date
                    ajaxCall();
                    self.items(cartData.items);
                }
            );
            this.items(customerData.get('cart')().items); //get cart items
            minicart.on('contentLoading', function () {
                self.shouldShowFastButton(false);
                self.fastDark(false);
            });
        },

        initObservable: function () {
            this._super();
            this.observe(this.observableProperties);
            return this;
        },

        fastClick: function (data, e) {
            var self = this;
            if (typeof Fast !== 'function') {
                console.error('Fast not loaded, please reload the page and try again.');
                return false;
            }

            // it's possible that we got an error
            // and not have cart or app id available
            if(self.cartId() && self.fastAppId()){                
                Fast.checkout({
                    appId: self.fastAppId(),
                    buttonId: e.target.id,
                    cartId: self.cartId(),
                    theme: self.fastDark()
                });
            }
        },

        fastDarkFunc: function () {
            return fastConfig.getBtnTheme() === 'dark';
        }
    });
});
