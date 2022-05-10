define(
    [
       'jquery',
       'Magento_Checkout/js/view/summary/abstract-total',
       'Magento_Checkout/js/model/quote',
       'Magento_Checkout/js/model/totals',
       'Magento_Catalog/js/price-utils'
    ],
    function ($,Component,quote,totals,priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Mconnect_Giftcard/checkout/summary/giftcode-discount'
            },
            totals: quote.getTotals(),
            isDisplayedGiftcodediscountTotal : function () {
                var quoteData = window.checkoutConfig.quoteData;
                var price = quoteData.giftcode_discount;
                if (price < 0) {
                    return true;
                }
                return false;
            },
            getGiftcodediscountTotal : function () {
                var quoteData = window.checkoutConfig.quoteData;
                var price = quoteData.giftcode_discount;
                return this.getFormattedPrice(price);
            }
        });
    }
);
