
define(
    [
        'Magento_Checkout/js/view/payment/default',
        //'ecollect_Core/js/model/set-analytics-information'
        //'MPcheckout',
        //'MPanalytics'
    ],
    function (Component, setAnalyticsInformation) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'ecollect_Core/payment/standard_redirect',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },
            isPaymentReady: function () {
                return this.paymentReady();
            },
            /**
             * Get action url for payment method.
             * @returns {String}
             */
            getActionUrl: function () {
                if (window.checkoutConfig.payment['ecollect_standard'] != undefined) {
                    return window.checkoutConfig.payment['ecollect_standard']['actionUrl'];
                }
                return '';
            },

            getBannerUrl: function () {
                if (window.checkoutConfig.payment['ecollect_standard'] != undefined) {
                    return window.checkoutConfig.payment['ecollect_standard']['bannerUrl'];
                }
                return '';
            },

            /**
             * Get url to logo
             * @returns {String}
             */
            getLogoUrl: function () {
                if (window.checkoutConfig.payment['ecollect_standard'] != undefined) {
                    return window.checkoutConfig.payment['ecollect_standard']['logoUrl'];
                }
                return '';
            },

            /**
             * Places order in pending payment status.
             */
            afterPlaceOrder: function () {
                window.location = this.getActionUrl();
            },
            /**
             * Places order in pending payment status.
             */
            placePendingPaymentOrder: function () {
                this.placeOrder();
            },
            initialize: function () {
                this._super();
                //setAnalyticsInformation.beforePlaceOrder(this.getCode());
            }

        });
    }
);
