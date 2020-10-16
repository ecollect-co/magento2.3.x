<?php

namespace ecollect\Core\Controller\Standard;

use ecollect\Core\Model\Core;
//use Magento\Framework\Controller\ResultFactory;

/**
 * Class Success
 *
 * @package ecollect\Core\Controller\Success
 */
class Success
    extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $_orderSender;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \ecollect\Core\Helper\Data
     */
    protected $_helperData;

    protected $_core;
    protected $_view;
    protected $_resultPageFactory;

    public $data = "data vacio";

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_catalogSession;

    /**
     * Page constructor.
     *
     * @param Core                                                $core
     * @param \Magento\Framework\App\Action\Context               $context
     * @param \Magento\Checkout\Model\Session                     $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory                   $orderFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Psr\Log\LoggerInterface                            $logger
     * @param \ecollect\Core\Helper\Data                       $helperData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param \ecollect\Core\Model\Core                        $core
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Psr\Log\LoggerInterface $logger,
        \ecollect\Core\Helper\Data $helperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ecollect\Core\Model\Core $core,
        \Magento\Catalog\Model\Session $catalogSession
    )
    {

        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_orderSender = $orderSender;
        $this->_logger = $logger;
        $this->_helperData = $helperData;
        $this->_scopeConfig = $scopeConfig;
        $this->_core = $core;
        $this->_catalogSession = $catalogSession;
        $this->_view = $context->getView();
        parent::__construct(
            $context
        );

    }
    protected function _getFormattedPaymentData($order_id, $data = [])
    {
        //file_put_contents("log.html","dentro de _getFormattedPaymentData order id $order_id<br>",FILE_APPEND);

        $arr['entityCode'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_ENTITY_ODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['apiKey'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_API_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['srvCode'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_SRV_CODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['srvCurrency'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_SRV_CURRENCY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['wdslProduction'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_WDSL_PRODUCTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['wdslTest'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_WDSL_TEST, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['testmode'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_TESTMODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //file_put_contents("log.html",json_encode($arr)."<br>antes de coreModel->getPaymentE",FILE_APPEND);
        $response = $this->_core->getPaymentE($order_id,$arr);
        //$payment = $response['response']['collection'];

        //file_put_contents("log.html","respuesta de coreModel->getPaymentE".json_encode($response)."<br>",FILE_APPEND);
        return $response;

        //return  $this->_statusHelper->formatArrayPayment($data, $payment, self::LOG_NAME);
    }

    /**
     * Controller action
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $data = $this->_getFormattedPaymentData($id);
        $dataTxt = json_encode($data);

        $this->_view->loadLayout(['default', "ecollect_standard_success"]);
        $this->_view->getLayout()->getBlock("ecollect_standard_success")->setKey($dataTxt);
        $this->_view->renderLayout();


    }

}