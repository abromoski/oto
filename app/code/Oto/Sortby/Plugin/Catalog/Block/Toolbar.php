<?php
namespace Oto\Sortby\Plugin\Catalog\Block;

class Toolbar
{
    public function aroundSetCollection(
    \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
    \Closure $proceed,
    $collection
    ) {
    $currentOrder = $subject->getCurrentOrder();
    $result = $proceed($collection);

    if ($currentOrder) {
        if ($currentOrder == 'price_desc') {
            $subject->getCollection()->setOrder('price', 'desc');
        } elseif ($currentOrder == 'price_asc') {
            $subject->getCollection()->setOrder('price', 'asc');
        } elseif ($currentOrder == 'position_asc') {
            $subject->getCollection()->addAttributeToSort('position', 'asc')->addAttributeToSort('entity_id', 'asc');
        } elseif ($currentOrder == 'position_desc') {
            $subject->getCollection()->addAttributeToSort('position', 'desc')->addAttributeToSort('entity_id', 'desc');
        }
    }

    return $result;
    }

}