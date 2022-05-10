/*global byteConvert*/
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'jquery/file-uploader'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('mage.mediaUploader', {

        /**
         *
         * @private
         */
        _create: function () {
            var
                self = this,
                imageTmpl = mageTemplate(this.element.find('[data-template=image]').html()),
                $dropPlaceholder = this.element.find('.uploader'),
                progressTmpl = mageTemplate('[data-template="progressbar"]');

            this.element.find('input[type=file]').fileupload({
                dataType: 'json',
                formData: {
                    'form_key': window.FORM_KEY
                },
                dropZone: '[data-tab-panel=image-management]',
                sequentialUploads: true,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: this.options.maxFileSize,

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                add: function (e, data) {
                    var
                        fileSize,
                        tmpl,
                        flag_oversize=0;

                    $.each(data.files, function (index, file) {
                        fileSize = typeof file.size == 'undefined' ?
                            $.mage.__('We could not detect a size.') :
                            file.size;
                            
                        flag_oversize = self.options.maxFileSize*1024 < file.size ?1:0;
                    });

                    if (flag_oversize) {
                        alert({
                            content: $.mage.__('File is oversize. Max upload size is '+self.options.maxFileSize+' KB')
                        });
                    } else {
                        $(this).fileupload('process', data).done(function () {
                            data.submit();
                        });
                    }
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                done: function (e, data) {
                    if (data.result && !data.result.error) {
                        self.element.find('input[type=hidden]').val(data.result.file);
                        
                        var tmpl = imageTmpl({
                            data: data.result
                        });

                        $(tmpl).data('image', data.result).insertBefore($dropPlaceholder);
                        $dropPlaceholder.hide();
                    } else {
                        alert({
                            content: $.mage.__('We don\'t recognize or support this file extension type.')
                        });
                    }
                    self.element.find('[data-role="loader"]').hide();
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                progress: function (e, data) {
                    self.element.find('[data-role="loader"]').show();
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                fail: function (e, data) {
                    self.element.find('[data-role="loader"]').hide();
                    alert({
                        content: $.mage.__('Something went wrong. Please try again..')
                    });
                }
            });

            this.element.find('input[type=file]').fileupload('option', {
                process: [{
                    action: 'load',
                    fileTypes: / ^image\/(gif|jpeg|png)$/
                }, {
                    action: 'resize',
                    maxWidth: this.options.maxWidth,
                    maxHeight: this.options.maxHeight
                }, {
                    action: 'save'
                }]
            });
            
            this.element.on('click', '[data-role=delete-button]', function (event) {
                self.element.find('input[type=hidden]').val('');
                self.element.find('[data-role="image"]').remove();
                self.element.find('.uploader').show();
            });
            
            if (this.element.find('input[type=hidden]').val() != '') {
                var basepath = this.element.find('input[type=hidden]').attr('mediaurl');
                var imagefile = this.element.find('input[type=hidden]').val();
                var imageurl = basepath + 'catalog/product/customgiftcard' + imagefile;
                
                var tmpl = imageTmpl({
                    data: { url: imageurl }
                });

                $(tmpl).insertBefore($dropPlaceholder);
                $dropPlaceholder.hide();
            }
        }
    });

    return $.mage.mediaUploader;
});

