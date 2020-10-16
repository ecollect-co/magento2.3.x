define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'ecollect_Core/js/model/iframe',
        'Magento_Checkout/js/model/full-screen-loader',
        //'ecollect_Core/js/model/set-analytics-information'
        //'MPanalytics'
    ],
    function (Component, ko, iframe, fullScreenLoader, setAnalyticsInformation) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'ecollect_Core/payment/standard_iframe',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,
            isInAction: iframe.isInAction,
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },
            isPaymentReady: function () {
                return this.paymentReady();
            },
            /**
             * Get action url for payment method iframe.
             * @returns {String}
             */
            getActionUrl: function () {
                if (window.checkoutConfig.payment['ecollect_standard'] != undefined) {
                    return window.checkoutConfig.payment['ecollect_standard']['actionUrl'];
                }
                return '';
            },
            /**
             * Get url to show banner
             * @returns {String}
             */
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
             * Get height iframne configured
             * @returns {String}
             */
            getConfigHeight: function () {
                if (window.checkoutConfig.payment['ecollect_standard'] != undefined) {
                    return window.checkoutConfig.payment['ecollect_standard']['iframe_height'];
                }
                return 710;
            },
            /**
             * Places order in pending payment status.
             */
            placePendingPaymentOrder: function () {
                var self = this;
                this.afterPlaceOrder = function () {
                    self.paymentReady(true);
                };
                if (this.placeOrder()) {
                    this.isInAction(true);
                    // capture all click events
                    document.addEventListener('click', iframe.stopEventPropagation, true);
                }
            },
            /**
             * Hide loader when iframe is fully loaded.
             * @returns {void}
             */
            iframeLoaded: function () {
                fullScreenLoader.stopLoader();
            },
            initialize: function () {
                this._super();
                setAnalyticsInformation.beforePlaceOrder(this.getCode());
            }
        });
    }
);
