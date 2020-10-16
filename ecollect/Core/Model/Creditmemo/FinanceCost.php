<?php
namespace ecollect\Core\Model\Creditmemo;

/**
 * Class FinanceCost
 *
 * @package ecollect\Core\Model\Creditmemo
 */
class FinanceCost
    extends \Magento\Sales\Model\Order\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $amount = $order->getFinanceCostAmount();
        $baseAmount = $order->getBaseFinanceCostAmount();
        if ($amount) {
            $creditmemo->setFinanceCostAmount($amount);
            $creditmemo->setBaseFinanceCostAmount($baseAmount);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount);
        }

        return $this;
    }
}