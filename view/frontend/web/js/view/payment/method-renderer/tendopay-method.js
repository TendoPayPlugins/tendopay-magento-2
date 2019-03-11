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

        redirectAfterPlaceOrder: false,

        /** Open window with  */
        showAcceptanceWindow: function (data, event) {
            window.open(
                $(event.currentTarget).attr('href'),
                'olcwhatispaypal',
                'toolbar=no, location=no,' +
                ' directories=no, status=no,' +
                ' menubar=no, scrollbars=yes,' +
                ' resizable=yes, ,left=0,' +
                ' top=0, width=400, height=350'
            );

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
