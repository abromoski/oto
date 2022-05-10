var config = {
    paths: {
        'bootstrap': 'Magento_Theme/js/bootstrap.bundle',
        'mainjs': 'Magento_Theme/js/oto-main-js',
        'mainjshome': 'Magento_Theme/js/oto-main-js-home',
        'scrollability': 'Magento_Theme/js/jScrollability.min',
        'progress': 'Magento_Theme/js/circle-progress',
        'owl': 'Magento_Theme/js/owl.carousel.min',
        'aos': 'Magento_Theme/js/aos'


    },

    shim: {
        'bootstrap': {
            'deps': ['jquery']
        },
        'mainjs': {
            'deps': ['jquery']
        },

        'mainjshome': {
            'deps': ['jquery']
        },


        'owl': {
            'deps': ['jquery']
        },

        'progress': {
            'deps': ['jquery']
        },
        'swipe': {
            'deps': ['jquery']
        },
        'scrollability': {
            'deps': ['jquery']
        }
    },




    config: {
        'mixins': {
            'Magento_Checkout/js/view/summary/cart-items': {
                'Magento_Checkout/js/view/summary/cart-items-mixin': true
            }
        }
    }

};


