<?php
namespace Mconnect\Giftcard\Model\Source\Giftcode;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Status implements OptionSourceInterface
{
   
    protected $giftcode;
   
    public function __construct(\Mconnect\Giftcard\Model\Giftcode $giftcode)
    {
        $this->giftcode = $giftcode;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->giftcode->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
