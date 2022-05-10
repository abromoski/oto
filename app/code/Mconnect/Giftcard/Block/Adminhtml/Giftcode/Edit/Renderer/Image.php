<?php
namespace Mconnect\Giftcard\Block\Adminhtml\Giftcode\Edit\Renderer;
 
use Magento\Framework\UrlInterface;

/**
 * CustomFormField Customformfield field renderer
 */
class Image extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $_urlBuilder;
    
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        UrlInterface $urlBuilder,
        $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('file');
    }
    
    public function getElementHtml()
    {
        $src = $this->getData('src');
        $url = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $src;
        
        $html = '';

            $html = $html.'<div style="width:154px;height:116px;margin:10px 0;border:2px solid #000" id="customdiv">';
            $html = $html.'<img src="'.$url.'" style="height: 112px; width: 150px;" />';
            $html = $html.'</div>';

        return $html;
    }
}
