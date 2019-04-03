define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, quote, urlBuilder, storage, errorProcessor, customer, fullScreenLoader) {
        'use strict';
        var agreementsConfig = window.checkoutConfig.checkoutAgreements;
        return function (messageContainer, method) {

            var serviceUrl,
                payload,
                paymentData = quote.paymentMethod();

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                    quoteId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData,
                    shippingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            }

             if (agreementsConfig.isEnabled) {
                if(jQuery(".payment-method._active .checkout-agreements input[type='checkbox']:checked").length == 0){
                    paymentData.extension_attributes = {agreement_ids: [""]};
                }else{
                    paymentData.extension_attributes = {agreement_ids: ["1"]};
                }
            }

            fullScreenLoader.startLoader();

            return storage.post(serviceUrl, JSON.stringify(payload))
                .done(function (data) {
                    $.ajax({
                        method: "POST",
                        url: window.checkoutConfig.payment['sdm_altapay'].url,
                        data: {
                            paytype: method,
                            cartid: quote.getQuoteId(),
                            orderid: data
                        },
                        dataType: 'json'
                    })
                        .done(function (jsonResponse) {
                            console.log(jsonResponse);
                            if (jsonResponse.result == 'success') {
                                window.location.href = jsonResponse.formurl;
                            } else {
                                fullScreenLoader.stopLoader();
                                alert(jsonResponse.message);
                                window.location.href = '/checkout/cart/';
                            }
                        });
                })
                .fail(function (response) {
                    errorProcessor.process(response, messageContainer);
                    fullScreenLoader.stopLoader();
                }
            );

        };
    }
);
