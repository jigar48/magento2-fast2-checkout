define(['uiComponent', 'jquery', 'ko', 'underscore', 'fastConfig'],
    function(Component, $, ko, _, fastConfigFactory) {
        'use strict';

        var fastConfig = fastConfigFactory();

        return Component.extend({

            initialize: function() {
                var self = this;
                this._super();
                self.shouldShowFastButton = ko.observable(fastConfig.shouldShowFastOnPDP());
                $(document).ready(function () {
                    $("#pdp-fast-button").css({
                        'width': ($("#product-addtocart-button").outerWidth() + 'px')
                    });
                    $("#pdp-fast-button").prependTo(".box-tocart .fieldset .actions");
                });
            },
            pdpFastClick: function(data, e) {

                // get the form node via jquery
                var productForm = $('form#product_addtocart_form');

                // validating form
                var validForm = productForm.validation('isValid');

                if (validForm) {
                    // construct a FormData object from the form node
                    // and extract the selected options
                    var formData = new FormData(productForm[0]);

                    var options = [];
                    var productOptions = [];
                    productOptions.push({
                        id: formData.get('product'),
                        options: options,
                        quantity: Number(formData.get('qty'))
                    });

                    for (var pair of formData.entries()) {
                        if (pair[0].includes('super_attribute')) {
                            productOptions = [];
                            options.push({
                                id: pair[0].replace(/\D/g, ''),
                                value: pair[1],
                            });
                            productOptions.push({
                                id: formData.get('product'),
                                options: options,
                                quantity: Number(formData.get('qty'))
                            });
                            // break;
                        }
                        if (pair[0].includes('bundle_option')) {
                            productOptions.push({
                                id: pair[1],
                                options: [],
                                quantity: Number(formData.get('qty'))
                            });
                        }
                    }
                    // Bail if Fast is not loaded
                    if (typeof Fast !== 'function') {
                        console.error('Fast not loaded, please reload the page and try again.');
                        return false;
                    }
                    // fast checkout
                    Fast.checkout({
                        appId: fastConfig.getAppId(),
                        buttonId: event.target.id,
                        products: productOptions
                    });
                }


                return true;
            },
            isFastDarkTheme: function() {
                return fastConfig.getBtnTheme() === 'dark';
            },
            shouldShowFastButton: function() {
                return fastConfig.shouldShowFastOnPDP();
            }
        });
    });
