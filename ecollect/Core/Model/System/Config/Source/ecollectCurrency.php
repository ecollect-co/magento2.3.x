<?php
namespace ecollect\Core\Model\System\Config\Source;

/**
 * Class Country
 *
 * @package ecollect\Core\Model\System\Config\Source
 */
class ecollectCurrency
    implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return available country array
     * @return array
     */
    public function toOptionArray()
    {
        $currency = [];
        $currency[] = ['value' => "COP", 'label' => __("Peso Colombia"), 'code' => 'COP'];

        //force order by key
        ksort($currency);

        return $currency;
    }

}
