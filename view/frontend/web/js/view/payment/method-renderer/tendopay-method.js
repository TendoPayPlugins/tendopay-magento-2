/**
 * TendoPay
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customize this module for your needs.
 *
 * @category   TendoPay
 * @package    TendoPay_TendopayPayment
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 */
define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'TendoPay_TendopayPayment/js/action/set-payment-method'
],function ($, Component, setPaymentMethod) {
    'use strict';

    return Component.extend({
        defaults:{
            'template':'TendoPay_TendopayPayment/payment/tendopay'
        },

        initialize: function () {
            $('body').append(
                '<div class="tendopay__popup__container" style="display: none;">' +
                '<div class="tendopay__popup__iframe-wrapper">' +
                '<div class="tendopay__popup__close"></div>' +
                '<iframe src="' + window.checkoutConfig.payment.tendopay.tendopayMarketingPopup + '" class="tendopay__popup__iframe"></iframe>' +
                '</div>' +
                '</div>'
            );

            $('.tendopay__popup__close').click(function () {
                $('.tendopay__popup__container').toggle();
            });

            this._super();
            return this;
        },

        redirectAfterPlaceOrder: false,

        /** Open window with  */
        showAcceptanceWindow: function (data, event) {
            $('.tendopay__popup__container').show();
            return false;
        },

        /** Returns payment acceptance mark link path */
        getPaymentAcceptanceMarkHref: function () {
            return window.checkoutConfig.payment.tendopay.paymentAcceptanceMarkHref;
        },

        /** Returns payment acceptance mark image path */
        getPaymentAcceptanceMarkSrc: function () {
            return window.checkoutConfig.payment.tendopay.paymentAcceptanceMarkSrc;
        },

        /** Returns payment acceptance mark message */
        getPaymentAcceptanceMarkMessage: function () {
            return window.checkoutConfig.payment.tendopay.paymentAcceptanceMarkMessage;
        },

        /** Returns payment acceptance mark message */
        getPaymentMethodVisibility: function () {
            return window.checkoutConfig.payment.tendopay.visibility;
        },

        afterPlaceOrder: function () {
            setPaymentMethod();
        }
    });
});
