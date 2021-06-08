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
    'mage/translate',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Paypal/js/action/set-payment-method',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote'
], function ($, $t, Component, additionalValidators, setPaymentMethodAction, customerData, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            'template': 'TendoPay_TendopayPayment/payment/tendopay'
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
        getMinTotalAmount: function () {
            return window.checkoutConfig.payment.tendopay.minTotalAmount;
        },

        continueToTendoPay() {
            if (additionalValidators.validate()) {
                //update payment method information if additional data was changed
                this.selectPaymentMethod();
                setPaymentMethodAction(this.messageContainer).done(
                    function () {
                        customerData.invalidate(['cart']);
                        $.mage.redirect(
                            window.checkoutConfig.payment.tendopay.redirectUrl
                        );
                    }
                );

                return false;
            }
        },

        isButtonEnabled() {
            return this.getCode() === this.isChecked() && this.checkMinTotalAmount();
        },

        getGrandTotal: function () {
            let totals = quote.getTotals()();

            if (totals) {
                return totals['grand_total'];
            }

            return quote['grand_total'];
        },

        checkMinTotalAmount() {
            return this.getGrandTotal() > this.getMinTotalAmount();
        },

        getMinTotalAmountMessage() {
            return $t('Min Total Order Amount %1₱').replace('%1', this.getMinTotalAmount);
        }
    });
});
