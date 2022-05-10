
/*global byteConvert*/
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'jquery/file-uploader'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('mage.gcMediaUploader', {

        /**
         *
         * @private
         */
        _create: function () {
            var
                self = this,
                imageTmpl = mageTemplate(this.element.find('[data-template=image]').html()),
                //$dropPlaceholder = this.element.find('.image-placeholder'),
                $dropPlaceholder = this.element.find('.uploader'),
                progressTmpl = mageTemplate('[data-template="uploader"]');

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
                        tmpl;

                    $.each(data.files, function (index, file) {
                        fileSize = typeof file.size == 'undefined' ?
                            $.mage.__('We could not detect a size.') :
                            byteConvert(file.size);

                        data.fileId = Math.random().toString(33).substr(2, 18);

                        tmpl = progressTmpl({
                            data: {
                                name: file.name,
                                size: fileSize,
                                id: data.fileId
                            }
                        });

                        $(tmpl).appendTo(self.element);
                    });

                    $(this).fileupload('process', data).done(function () {
                        data.submit();
                    });
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
                    self.element.find('#' + data.fileId).remove();
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                progress: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10),
                        progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';

                    self.element.find(progressSelector).css('width', progress + '%');
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                fail: function (e, data) {
                    var progressSelector = '#' + data.fileId;

                    self.element.find(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                        .delay(2000)
                        .hide('highlight')
                        .remove();
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
                self.element.find('[data-role="gcimage"]').remove();
                self.element.find('.uploader').show();
            });
            
            if (this.element.find('input[type=hidden]').val() != '') {
                var basepath = this.element.find('input[type=hidden]').attr('mediaurl');
                var imagefile = this.element.find('input[type=hidden]').val();
                var imageurl = basepath + 'catalog/product' + imagefile;
                
                var tmpl = imageTmpl({
                    data: { url: imageurl }
                });

                $(tmpl).insertBefore($dropPlaceholder);
                $dropPlaceholder.hide();
            }
        }
    });

    return $.mage.gcMediaUploader;
});

