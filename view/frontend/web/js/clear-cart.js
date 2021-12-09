define([
    'jquery',
    'fastConfig',
], function($, fastConfigFactory) {
    var fastConfig = fastConfigFactory();

    // Bail if Fast is not loaded
    if(typeof Fast !== 'function') {
        console.error('Fast not loaded, please reload the page and try again.');
        return false;
    }

    var fast = new Fast();
    fast.addEventListener("user_event", function(event) {
        var cartClearingEvents = [
            "Checkout - Order Created",
            "Checkout - Order Updated",
        ];
        // Handle events here
        if(cartClearingEvents.includes(event.name)) {
            // Hit custom endpoint to clear the cart
            $.post(fastConfig.getClearCartUrl());
        }
    });
});