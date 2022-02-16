require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'text!TendoPay_TendopayPayment/template/tendopay-modal-popup.html',
        'mage/translate',
        'domReady!'
    ],
    function($, modal, popupTpl, $t) {

        let title = window.tendoInterestType === 'regular' ?
                        $t('Buy now, pay later<br>on Installments') :
                        $t('Shop today at 0%<br>on Installments');
        let contentItemLeftText = window.tendoInterestType === 'regular' ?
                        $t('Low<br>Interest') :
                        $t('0% Interest,<br>Really');
        let footerText = $t('You must be over 18, a resident of the Philippines and meet additional criteria to quality. Late<br> fees apply. Click here for complete terms.  © 2022 TendoPay');
        let buttonText = $t('How it works');
        let buttonLink = 'https://tendopay.ph/how-it-works';

        let options = {
            type: 'popup',
            title: title,
            responsive: true,
            innerScroll: true,
            modalClass: 'tendo-modal-product-info',
            popupTpl: popupTpl,
            footerText: footerText,
            contentItemLeftText: contentItemLeftText,
            buttons: [{
                text: buttonText,
                class: 'tendopay-modal-button',
                click: function () {
                    this.closeModal();
                    window.open(buttonLink,'_blank');
                }
            }]
        };

        var tendopayModal  = modal(options, $('#tendopay-modal'));

        $(".show-popup").on('click',function(){
            // debugger;
            $totalInstallments = $(this).data('total_installments') ?? 1;
            if(!$totalInstallments) {
                $totalInstallments = 1;
            }
            $("#tendopay-modal-total_installments").html($totalInstallments);
            $("#tendopay-modal").modal("openModal");
        });
    }
);
