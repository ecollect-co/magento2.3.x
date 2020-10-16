<?php

namespace ecollect\Core\Controller\Standard;

/**
 * Class Pay action controller to pay order with MP
 *
 * @package Mercadopago\Core\Controller\Standard
 */
class Pay
    extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \ecollect\Core\Model\Standard\PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \ecollect\Core\Model\Standard\PaymentFactory    $paymentFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ecollect\Core\Model\Standard\PaymentFactory $paymentFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {

        //file_put_contents("pay.html","Pay constructor");
        $this->_paymentFactory = $paymentFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * /Execute action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        //file_put_contents("pay.html","Pay execute", FILE_APPEND);
        $standard = $this->_paymentFactory->create();
        $array_assign = $standard->postPago();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($array_assign['status'] != 400) {
        //if(true){
            $resultRedirect->setUrl($array_assign['init_point']);
        } else {
            $typeCheckout = $this->_scopeConfig->getValue('payment/ecollect_standard/type_checkout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($typeCheckout == 'lightbox') {
                $resultRedirect->setUrl($this->_url->getUrl('ecollect/standard/failureRedirect'));
            } else {
                $resultRedirect->setUrl($this->_url->getUrl('ecollect/standard/failure'));
            }
        }

        return $resultRedirect;
    }
}