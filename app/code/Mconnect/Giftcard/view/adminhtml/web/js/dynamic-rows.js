
define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'mage/apply/main'
], function (_, dynamicRows, mage) {
    'use strict';

    return dynamicRows.extend({
        
        deleteRecord: function (index, recordId) {
            this._super(index, recordId);
            
            setTimeout(function () {
                if (mage) {
                    mage.apply();
                }
            }, 100);
        }
        
    });
});

