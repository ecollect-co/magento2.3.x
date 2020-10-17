<?php
namespace ecollect\Core\Block\Calculator;

class CalculatorForm
    extends \Magento\Framework\View\Element\Template
{

    const CALCULATOR_JS = 'ecollect/ecollect_calculator.js';

    /**
     * @var $_helperData \ecollect\Core\Helper\Data
     */
    protected $_helperData;

    protected $_amount;


    /**
     * CalculatorForm constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \ecollect\Core\Helper\Data                    $helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context    $context,
        \ecollect\Core\Helper\Data                       $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helperData = $helper;
    }

    /**
     * Return the PublicKey from ecollect checkout custom configuration
     *
     * @return mixed
     */
    public function getPublicKey()
    {
        $key = $this->_helperData->getPublicKey();
        return $key;
    }

    /**
     * return the Payment methods token configured
     *
     * @return string
     */
    public function getPaymentMethods()
    {
        $accessToken = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_ACCESS_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->_helperData->getMercadoPagoPaymentMethods($accessToken);
    }

    /**
     * Check if current requested URL is secure
     *
     * @return boolean
     */
    public function isCurrentlySecure()
    {
        return $this->_storeManager->getStore()->isCurrentlySecure();
    }

    public function setAmount($amount){
        $this->_amount = $amount;
    }

    /**
     * return the current value of amount
     *
     * @return mixed|bool
     */
    public function getAmount()
    {
        return $this->_amount;
    }

}