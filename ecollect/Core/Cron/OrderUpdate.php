<?php

namespace ecollect\Core\Cron;

use ecollect\Core\Lib\eCollectApi;
use Magento\Sales\Model\Order;

class OrderUpdate
{

    /**
     * @var \ecollect\Core\Helper\StatusUpdate
     */
    protected $_statusHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \ecollect\Core\Helper\Data
     */
    protected $_helper;

    /**
     * @var \ecollect\Core\Model\Core
     */
    protected $_core;

    const LOG_FILE = 'ecollect-order-synchronized.log';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \ecollect\Core\Helper\StatusUpdate $statusUpdate,
        \ecollect\Core\Helper\Data $helper,
        \ecollect\Core\Model\Core $core,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_statusHelper = $statusUpdate;
        $this->_helper = $helper;
        $this->_core = $core;
        $this->_eventManager = $eventManager;
    }

    public function execute(){
       $hours = $this->_scopeConfig->getValue('payment/ecollect/number_of_hours');

        // filter to date:
        $fromDate = date('Y-m-d H:i:s', strtotime('-'.$hours. ' hours'));
        $toDate = date('Y-m-d H:i:s', strtotime("now"));

        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->join(
                ['payment' => 'sales_order_payment'],
                'main_table.entity_id=payment.parent_id',
                ['payment_method' => 'payment.method']
            )
            ->addFieldToFilter('status' ,["nin" => ['canceled','complete']])
            ->addFieldToFilter('created_at', ['from'=>$fromDate, 'to'=>$toDate])
        ;
        file_put_contents("cron.html","<h1>Orders to be processed</h1><br>");
        // For all Orders to analyze
        foreach($collection as $orderByPayment){
            $order = $orderByPayment;
            $paymentOrder = $order->getPayment();
            $infoPayments = $paymentOrder->getAdditionalInformation();

            $method = $paymentOrder->getMethod();

            $order->canCreditmemo();

            $order_id = $order->getEntityId();
            file_put_contents("cron.html","order id $order_id<br>",FILE_APPEND);

            if ($method == "ecollect_custom" || $method == "ecollect_customticket" || $method == "ecollect_standard"){
                $status = $order->getStatus();
                file_put_contents("cron.html","method $method status ". $status ."<br>",FILE_APPEND);
                if ($status == "pending"){
                    if (file_exists("ecoi")) {
                        ////file_put_contents("log.html","before items <br>",FILE_APPEND);
                        $itemsTxt = file_get_contents("ecoi");
                        ////file_put_contents("log.html","items txt $itemsTxt <br>",FILE_APPEND);
                        $itemsJson = json_decode($itemsTxt);
                        ////file_put_contents("log.html","items json ".json_encode($itemsJson)." <br>",FILE_APPEND);
                        $items = get_object_vars($itemsJson);
                        //$ticketId = $items[$order_id];
                        foreach ($items as $key=>$ticketId) {
                            //file_put_contents("cron.html","order id $key => ticketId $ticketId <br>",FILE_APPEND);
                            //file_put_contents("cron.html","$key+1 == $order_id+1 <br>",FILE_APPEND);
                            if ($key+1 == $order_id+1){
                                if (!($order->getId()) || $order->getStatus() == 'canceled') {
                                    file_put_contents("cron.html","order DOES NOT EXIST<br>",FILE_APPEND);
                                    break;
                                }
                                file_put_contents("cron.html","FOUND --> to be processed<br>",FILE_APPEND);

                                $arr = $this->_getFormattedPaymentData($key,$ticketId);
                                file_put_contents("cron.html","arr ".json_encode($arr)."<br>",FILE_APPEND);

                                $currentStatus = $order->getPayment()->getAdditionalInformation('status');
                                $iguales = ($arr['status'] == $currentStatus) ? "iguales" : "distintos";
                                file_put_contents("cron.html","status ".$arr['status']." currentStatus $currentStatus $iguales<br>",FILE_APPEND);

                                $isStatusUpdated = $this->_statusHelper->isStatusUpdated()? "true" : "false";
                                file_put_contents("cron.html","isStatusUpdated $isStatusUpdated<br>",FILE_APPEND);

                                $status1 = $arr['status'];
                                if (!($status1 == false)) {
                                    if ($status1 == "approved"){
                                        $orderState =  Order::STATE_PROCESSING;
                                        $order->setState($orderState)->setStatus(Order::STATE_PROCESSING);
                                        $order->save();
                                    }
                                    else if ($status1 == "cancelled"){
                                        $order->cancel()->save();
                                    }
                                }
                                else {
                                    file_put_contents("cron.html","CANCEL ORDER <br>",FILE_APPEND);
                                    $order->cancel()->save();
                                }
                                break;
                            }
                        }
                        ////file_put_contents("log.html","ticketId = $ticketId <br>",FILE_APPEND);
                    }
                }
                file_put_contents("cron.html","<br>",FILE_APPEND);

                if (isset($infoPayments['merchant_order_id']) && $order->getStatus() !== 'complete') {
                    file_put_contents("cron.html","merchant_order_id ".$infoPayments['merchant_order_id']." status ".$order->getStatus()."<br>",FILE_APPEND);
                }
            }
        }
    }

    protected function _getFormattedPaymentData($order_id, $ticketId, $data = [])
    {
        $arr['entityCode'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_ENTITY_ODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['apiKey'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_API_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['srvCode'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_SRV_CODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['srvCurrency'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_SRV_CURRENCY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['wdslProduction'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_WDSL_PRODUCTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['wdslTest'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_WDSL_TEST, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $arr['testmode'] = $this->_scopeConfig->getValue(\ecollect\Core\Helper\Data::XML_PATH_TESTMODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        ////file_put_contents("log.html",json_encode($arr));
        file_put_contents("cron.html","arr ".json_encode($arr)."<br>",FILE_APPEND);

        $entityCode = $arr['entityCode'];
        $apiKey = $arr['apiKey'];
        $srvCode = $arr['srvCode'];
        $srvCurrency = $arr['srvCurrency'];
        $wdslProduction = $arr['wdslProduction'];
        $wdslTest = $arr['wdslTest'];
        $testmode = $arr['testmode'];

        file_put_contents("cron.html","before eCollectApi <br>",FILE_APPEND);

        $eCollect = new \ecollect\Core\Lib\eCollectApi();
        $eCollect->setEntityCode($entityCode);
        $eCollect->setTicketId($ticketId);
        $eCollect->setSrvCode($srvCode);
        $eCollect->setSrvCurrency($srvCurrency);
        $eCollect->setWdslProduction($wdslProduction);
        $eCollect->setWdslTest($wdslTest);
        $eCollect->setSandbox($testmode);

        $arr = array(); 
        $data_json =  $api->getSessionToken();
        if($data_json->ReturnCode == "SUCCESS"){
            $response = $eCollect->getTransactionInformation($data_json->SessionToken, $order_id);
            $transactionState = $eCollect->getTransactionState();
            $returnCode = $eCollect->getReturnCode();
            if ($returnCode == "SUCCESS") {
                switch ($transactionState) {
                    case "OK":
                        $returnCode = "approved";
                        break;
                    case "CAPTURED":
                        $returnCode = "captured";
                        break;
                    case "PENDING":
                        $returnCode = "pending";
                        break;
                    case "FAILED":
                        $returnCode = "cancelled";
                        break;
                    case "NOT_AUTHORIZED":
                        $returnCode = "cancelled";
                        break;
                    case "EXPIRED":
                        $returnCode = "cancelled";
                        break;						
                    default:
                        $returnCode = "cancelled";
                }
            }
            else {
                $returnCode = false;
            }

            $arr["status"] = $returnCode;
            $arr["status_detail"] = $returnCode;
            $arr["external_reference"] = $order_id;
            $arr["id"] = $ticketId;
            ////file_put_contents("log.html",json_encode($response),FILE_APPEND);
        }else{
            //echo "failed to result:" . $data_json->ReturnCode . "<br>";
            
        }

        return $arr;


        //return  $this->_statusHelper->formatArrayPayment($data, $payment, self::LOG_NAME);
    }

    /**
     * @param $order \Magento\Sales\Model\ResourceModel\Order
     * @param $statusOrder
     * @param $paymentOrder
     */
    protected function _updateOrder($order, $statusOrder, $paymentOrder){
        $order->setState($this->_statusHelper->_getAssignedState($statusOrder));
        $order->addStatusToHistory($statusOrder, $this->_statusHelper->getMessage($statusOrder, $statusOrder), true);
        $order->sendOrderUpdateEmail(true, $this->_statusHelper->getMessage($statusOrder, $paymentOrder));
        $order->save();
    }

    protected function getDataPayments($merchantOrderData)
    {
        $data = array();
        foreach ($merchantOrderData['payments'] as $payment) {
            $data = $this->getFormattedPaymentData($payment['id'], $data);
        }

        return $data;
    }

    protected function getFormattedPaymentData($paymentId, $data = [])
    {
        $response = $this->_core->getPayment($paymentId);
        if ($response['status'] == 400 || $response['status'] == 401) {
            return [];
        }
        $payment = $response['response']['collection'];

        return $this->_statusHelper->formatArrayPayment($data, $payment, self::LOG_FILE);
    }

}