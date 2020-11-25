<?php
namespace ecollect\Core\Controller\Notifications;

/**
 * Class Standard
 *
 * @package ecollect\Core\Controller\Notifications
 */

use ecollect\Core\Model\ResourceModel\Item;
use ecollect\Core\Model\ResourceModel\Item\Collection;

use Magento\Sales\Model\Order;

class Standard
    extends \Magento\Framework\App\Action\Action

{
    /**
     * @var \ecollect\Core\Model\Standard\PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @var \ecollect\Core\Helper\
     */
    protected $coreHelper;

    /**
     * @var \ecollect\Core\Model\Core
     */
    protected $coreModel;

    /**
     *log file name
     */
    const LOG_NAME = 'standard_notification';

    protected $_finalStatus = ['rejected', 'cancelled', 'refunded', 'charge_back'];
    protected $_notFinalStatus = ['authorized', 'process', 'in_mediation'];

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    protected $_checkoutSession;
    protected $_itemFactory;
    protected $_itemCollectionFactory;

    /**
     * @var \ecollect\Core\Helper\StatusUpdate
     */
    protected $_statusHelper;
    protected $_order;
    protected $_scopeConfig;

    /**
     * Standard constructor.
     *
     * @param \Magento\Framework\App\Action\Context           $context
     * @param \ecollect\Core\Model\Standard\PaymentFactory $paymentFactory
     * @param \ecollect\Core\Helper\Data                   $coreHelper
     * @param \ecollect\Core\Model\Core                    $coreModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \ecollect\Core\Model\Standard\PaymentFactory $paymentFactory,
        \ecollect\Core\Helper\Data $coreHelper,
        \ecollect\Core\Helper\StatusUpdate $statusHelper,
        \ecollect\Core\Model\Core $coreModel,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        //file_put_contents("payLog.html","execute");
        $this->_paymentFactory = $paymentFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->coreHelper = $coreHelper;
        $this->coreModel = $coreModel;
        $this->_orderFactory = $orderFactory;
        $this->_statusHelper = $statusHelper;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    protected function _emptyParams($p1, $p2)
    {
        return (empty($p1) || empty($p2));
    }

    protected function _isValidResponse($response)
    {
        return ($response['status'] == 200 || $response['status'] == 201);
    }

    protected function _responseLog()
    {
        $this->coreHelper->log("Http code", self::LOG_NAME, $this->getResponse()->getHttpResponseCode());
    }

//_generateCreditMemo

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
        $response = $this->coreModel->getPaymentE($order_id,$arr);
        //$payment = $response['response']['collection'];

        //file_put_contents("log.html","respuesta de coreModel->getPaymentE".json_encode($response)."<br>",FILE_APPEND);
        return $response;

        //return  $this->_statusHelper->formatArrayPayment($data, $payment, self::LOG_NAME);
    }

    protected function _shipmentExists($shipmentData, $merchantOrder)
    {
        return (!empty($shipmentData) && !empty($merchantOrder));
    }

    protected function _getOrder()
    {
        $orderIncrementId = $this->_checkoutSession->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);

        return $order;
    }
    /**
     * Controller Action
     */
    public function execute()
    {

        //file_put_contents("payLog.html","execute",FILE_APPEND);

        $request = $this->getRequest();
        //notification received
        $this->coreHelper->log("Standard Received notification", self::LOG_NAME, $request->getParams());

        $shipmentData = '';
        $merchantOrder = '';
        $id = $request->getParam('id');
        $topic = $request->getParam('topic');

        if ($this->_emptyParams($id, $topic)) {
            $this->coreHelper->log("Merchant Order not found", self::LOG_NAME, $request->getParams());
            $this->getResponse()->setBody("Merchant Order not found.");
            $this->getResponse()->setHttpResponseCode(\ecollect\Core\Helper\Response::HTTP_NOT_FOUND);

            return;
        }

        if ($topic == 'merchant_order') {
            $response = $this->coreModel->getMerchantOrder($id);
            $this->coreHelper->log("Return merchant_order", self::LOG_NAME, $response);
            if (!$this->_isValidResponse($response)) {
                $this->_responseLog();

                return;
            }

            $merchantOrder = $response['response'];
            if (count($merchantOrder['payments']) == 0) {
                $this->_responseLog();

                return;
            }
            $data = $this->_getDataPayments($merchantOrder);
            $statusFinal = $this->_statusHelper->getStatusFinal($data['status'], $merchantOrder);
            $shipmentData = $this->_statusHelper->getShipmentsArray($merchantOrder);

        } elseif ($topic == 'payment') {
            //file_put_contents("log.html","antes de _getFormattedPaymentData <br>");
            $data = $this->_getFormattedPaymentData($id);
            $statusFinal = $data['status'];
            //file_put_contents("log.html","data ".json_encode($data)."<br>",FILE_APPEND);
            //file_put_contents("log.html","statusFinal $statusFinal<br>",FILE_APPEND);

        } else {
            $this->_responseLog();

            return;
        }

        // if this happens, we need to generate a credit memo
        if (isset($data["amount_refunded"]) && $data["amount_refunded"] > 0) {
            $this->_statusHelper->generateCreditMemo($data);
        }

        if ($this->_shipmentExists($shipmentData, $merchantOrder)) {
            $this->_eventManager->dispatch(
                'ecollect_standard_notification_before_set_status',
                ['shipmentData' => $shipmentData, 'orderId' => $merchantOrder['external_reference']]
            );
        }

        $this->_order = $this->coreModel->_getOrder($data['external_reference']);
        if (!$this->_orderExists() || $this->_order->getStatus() == 'canceled') {
            return;
        }
        $status1 = $data['status'];
        if (!($status1 == false)) {
            if ($status1 == "approved"){
                $orderState =  Order::STATE_PROCESSING;
                $this->_order->setState($orderState)->setStatus(Order::STATE_PROCESSING);
                $this->_order->save();
            }
            else if ($status1 == "cancelled"){
                $this->_order->cancel()->save();
            }
        }
        else {
            file_put_contents("cron.html","CANCEL ORDER <br>",FILE_APPEND);
            $this->_order->cancel()->save();
        }


        if ($this->_shipmentExists($shipmentData, $merchantOrder)) {
            $this->_eventManager->dispatch('ecollect_standard_notification_received',
                ['payment'        => $data,
                 'merchant_order' => $merchantOrder]
            );
        }

        $this->_responseLog();

        if ($statusFinal == 'approved' || $statusFinal == 'pending' || $statusFinal == 'captured'){
            $this->_redirect('checkout/onepage/success');

            //$pTxt = $data["ecollectTitle"];
            //$pTxt .= "|".$data["ecollectTicket"];
            //$pTxt .= "|".$data["ecollectTrazabilityCode"];
            //$pTxt .= "|".$data["ecollectTransactionState"];
            //$pTxt .= "|".$data["ecollectBankProcessDate"];
            //$pTxt .= "|".$data["ecollectBankName"];
            //$this->_checkoutSession->setMyValue($pTxt);

            //$par = array();
            //$par["t"] = $data["ecollectTitle"];
            //$par["tk"] = $data["ecollectTicket"];
            //$par["tc"] = $data["ecollectTrazabilityCode"];
            //$par["ts"] = $data["ecollectTransactionState"];
            //$par["bpd"] = $data["ecollectBankProcessDate"];
            //$par["bn"] = $data["ecollectBankName"];
            //$this->_checkoutSession->setMyValue($pTxt);

            //$dataTxt = "?t=".urlencode($data["ecollectTitle"]);
            //$dataTxt .= "&tk=".urlencode($data["ecollectTicket"]);
            //$dataTxt .= "&tc=".urlencode($data["ecollectTrazabilityCode"]);
            //$dataTxt .= "&ts=".urlencode($data["ecollectTransactionState"]);
            //$dataTxt .= "&bpd=".urlencode($data["ecollectBankProcessDate"]);
            //$dataTxt .= "&bn=".urlencode($data["ecollectBankName"]);
            //$urlRedirect = 'ecollect/standard/success/'.$dataTxt;
            //file_put_contents("st.html","<br>$urlRedirect<br>",FILE_APPEND);

            //$this->_redirect($urlRedirect, ['_current' => true]);
        } else {
            $this->_redirect('checkout/onepage/failure/');
            //$this->_redirect('ecollect/standard/failure/');
        }

    }

    /**
     * Collect data from notification content
     *
     * @param $merchantOrder
     *
     * @return array
     */
    protected function _getDataPayments($merchantOrder)
    {
        $data = array();
        foreach ($merchantOrder['payments'] as $payment) {
            $response = $this->coreModel->getPayment($payment['id']);
            $payment = $response['response']['collection'];
            $data = $this->_statusHelper->formatArrayPayment($data, $payment, self::LOG_NAME);
        }
        return $data;
    }

    public static function _dateCompare($a, $b)
    {
        $t1 = strtotime($a['value']);
        $t2 = strtotime($b['value']);

        return $t2 - $t1;
    }

    protected function _orderExists()
    {
        if ($this->_order->getId()) {
            return true;
        }
        $this->coreHelper->log(\ecollect\Core\Helper\Response::INFO_EXTERNAL_REFERENCE_NOT_FOUND, self::LOG_NAME, $this->_requestData->getParams());
        $this->getResponse()->getBody(\ecollect\Core\Helper\Response::INFO_EXTERNAL_REFERENCE_NOT_FOUND);
        $this->getResponse()->setHttpResponseCode(\ecollect\Core\Helper\Response::HTTP_NOT_FOUND);
        $this->coreHelper->log("Http code", self::LOG_NAME, $this->getResponse()->getHttpResponseCode());

        return false;
    }
}