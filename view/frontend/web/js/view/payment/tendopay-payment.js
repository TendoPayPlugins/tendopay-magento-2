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
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
],function (Component,renderList) {
    'use strict';
    renderList.push({
        type : 'tendopay',
        component : 'TendoPay_TendopayPayment/js/view/payment/method-renderer/tendopay-method'
    });

    return Component.extend({});
});
