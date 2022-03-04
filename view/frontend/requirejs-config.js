var config = {
    paths: {
        'tendoPopup': 'TendoPay_TendopayPayment/js/tendo-popup',
    },
    shim: {
        'tendoPopup': {
            deps: [
                'jquery',
                'Magento_Ui/js/modal/modal'
            ]
        }
    }
}
