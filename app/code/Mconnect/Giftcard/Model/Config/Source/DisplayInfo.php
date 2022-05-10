<?php
namespace Mconnect\Giftcard\Model\Config\Source;

class DisplayInfo implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'amount', 'label' => __('Gift Card value')],
            ['value' => 'giftcard_template_id', 'label' => __('Gift Card template')],
            ['value' => 'customer_name', 'label' => __('Sender name')],
            ['value' => 'recipient_name', 'label' => __('Recipient name')],
            ['value' => 'recipient_email', 'label' => __('Recipient email address')],
            ['value' => 'message', 'label' => __('Custom message')],
            ['value' => 'day_to_send', 'label' => __('Day to send')],
            ['value' => 'timezone_to_send', 'label' => __('Time zone')],
        ];
    }
}
