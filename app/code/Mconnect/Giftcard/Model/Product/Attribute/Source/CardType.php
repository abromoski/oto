<?php
namespace Mconnect\Giftcard\Model\Product\Attribute\Source;

class CardType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * {@inheritdoc}
     */
     
    const VIRTUAL   = 1;
    const PHYSICAL  = 2;
    const COMBINED  = 3;
    
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                ['label' => __('Virtual'), 'value' => Self::VIRTUAL],
                ['label' => __('Physical'), 'value' => Self::PHYSICAL],
                ['label' => __('Combined'), 'value' => Self::COMBINED],
            ];
        }
        return $this->_options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
