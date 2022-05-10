/*
 *  @category	Techflarestudio
 *  @author		Wasalu Duckworth <info@magebit.com>
 *  @copyright  Copyright (c) 2021 Techflarestudio, Ltd. 			(https://techflarestudio.com)
 *  @license	http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 *
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Oto_Checkoutdefault/js/model/checkout-data-resolver': true
            }
        }
    }
};
