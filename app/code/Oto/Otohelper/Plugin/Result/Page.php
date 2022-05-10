<?php
namespace Oto\Otohelper\Plugin\Result;

use Magento\Framework\App\ResponseInterface;

class Page
{
private $context;

public function __construct(
\Magento\Framework\View\Element\Context $context
) {
$this->context = $context;
}

public function beforeRenderResult(
\Magento\Framework\View\Result\Page $subject,
ResponseInterface $response
){
   
   $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
   $product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
   
 if(!empty($product)){
   $attribute_value = $product->getAttributeText('moment_select'); 

   $subject->getConfig()->addBodyClass($attribute_value);
 }

return [$response];
}
}