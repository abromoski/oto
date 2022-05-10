<?php

namespace Oto\Sortby\Plugin\Catalog\Model;

class Config
{
    public function afterGetAttributeUsedForSortByArray(
    \Magento\Catalog\Model\Config $catalogConfig,
    $options
    ) {
       /* unset($options['position']);*/
       unset($options['position']);
        unset($options['price']);
        unset($options['name']);
        $options['position_asc'] = __('Featured');
        $options['price_asc'] = __('Price Low to High');
        $options['price_desc'] = __('Price High to Low');
       
        /*$options['position_desc'] = __('Position Desc');*/
        return $options;

    }

}