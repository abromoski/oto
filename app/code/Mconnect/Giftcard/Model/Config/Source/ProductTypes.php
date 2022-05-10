<?php
namespace Mconnect\Giftcard\Model\Config\Source;

use Magento\Catalog\Model\Product\TypeFactory;

class ProductTypes implements \Magento\Framework\Option\ArrayInterface
{
    protected $_typeFactory;
    
    public function __construct(
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory
    ) {
        $this->_typeFactory = $typeFactory;
    }
    
    public function toOptionArray()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );

        foreach ($types as $typeId => $type) {
            $splitButtonOptions[] = ['value' => $typeId, 'label' => __($type['label'])];
        }

        return $splitButtonOptions;
    }
}
