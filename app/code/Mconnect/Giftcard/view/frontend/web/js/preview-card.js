define([
    'jquery',
    'underscore',
    'jquery/ui'
], function ($, _) {
    'use strict';

    $.widget('mage.previewCard', {
        _create: function () {
            this._on({
                'click': '_showPopup'
            });
        },

        _initModal: function () {
            var self = this;

            this.modal = $('<div id="giftcard_product_preview"/>').modal({
                title: $.mage.__('Preview Card'),
                type: 'popup',
                buttons: [],
                opened: function () {
                    $(this).parent().addClass('modal-content-new-attribute');
                    self.iframe = $('<iframe id="giftcard_product_preview_container">').attr({
                        src: self._prepareUrl(),
                        frameborder: 0
                    });
                    
                    self.iframe.load(function () {
                        $('#preview_loader', window.parent.document).remove();
                    });
                    
                    self.modal.append('<div class="loading-mask" data-role="loader" id="preview_loader"><div class="loader"><img src="'+self.options.loaderurl+'"></div></div>');
                    
                    self.modal.append(self.iframe);
                    self._changeIframeSize();
                    $(window).off().on('resize', _.debounce(self._changeIframeSize.bind(self), 400));
                },
                closed: function () {
                    var doc = self.iframe.get(0).document;

                    if (doc && $.isFunction(doc.execCommand)) {
                        //IE9 break script loading but not execution on iframe removing
                        doc.execCommand('stop');
                        self.iframe.remove();
                    }
                    //self.modal.data('modal').modal.remove();
                }
            });
        },

        _getHeight: function () {
            return parseInt(window.innerHeight*0.80);
        },

        _getWidth: function () {
            return this.modal.width();
        },

        _changeIframeSize: function () {
            this.modal.parent().outerHeight(this._getHeight());
            this.iframe.outerHeight(this._getHeight());
            this.iframe.outerWidth(this._getWidth());

        },

        _prepareUrl: function () {
            return this.options.url +"?"+$('#product_addtocart_form').serialize();
        },

        _showPopup: function () {
            var isValid = $('#product_addtocart_form').validation('isValid');
            if (isValid) {
                this._initModal();
                this.modal.modal('openModal');
            }
        }
    });

    return $.mage.previewCard;
});

