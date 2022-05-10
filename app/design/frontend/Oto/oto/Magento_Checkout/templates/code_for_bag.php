<?php $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
 

$grandTotal = $cart->getQuote()->getGrandTotal();
$hundred= 100;
$required = ($hundred - $grandTotal);
if(isset($grandTotal)):
if ($grandTotal >= 100): ?>
<p style="text-align:center">You will recieve a free Hand Balm with your order</p>
<?php else: ?>
    <p style="text-align:center;font-size:18px">Spend £<?php echo $required ?> more to receive a <b>FREE Hand Balm</b> worth £29. </p>
    <?php endif;?>
    <?php endif;?>

<?php
    $productInfo = $this->_cart->getQuote()->getItemsCollection();
    $poo="yes";
    foreach ($productInfo as $item){
       $item->getProductId();
       if(($item->getProductId() == '37') && ($poo == "yes") : ?>
       <p style="text-align:center">You will recieve a free Hand Balm with your order</p>
       <? $poo="wee"; ?>
       <?php elseif(($item->getProductId() == '38') && ($poo == "yes") : ?>: ?>
        <p style="text-align:center">You will recieve a free Hand Balm with your order</p>
       <? $poo="wee"; ?>
       <?php endif;
       }
       ?>
       
       

     