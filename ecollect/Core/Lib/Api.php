<?php
namespace ecollect\Core\Lib;
/**
 * ecollect Integration Library
 * Access ecollect for payments integration
 *
 * @author hcasatti
 *
 */

use ecollect\Core\Lib\eCollectApi;
use \ecollect\Core\Model\ItemFactory;

class Api {

    /**
     *
     */
    const version = "0.3.3";

    /**
     * @var mixed
     */
    private $client_id;
    /**
     * @var mixed
     */
    private $client_secret;
    /**
     * @var mixed
     */
    private $ll_access_token;
    /**
     * @var
     */
    private $access_data;
    /**
     * @var bool
     */
    private $sandbox = FALSE;

    /**
     * @var null
     */
    private $_platform = null;
    /**
     * @var null
     */
    private $_so = null;
    /**
     * @var null
     */
    private $_type = null;

    private $itemFactory;

    /**
     * \ecollect\Core\Lib\Api constructor.
     */
    public function __construct() {

        /*$i = func_num_args();

        if ($i > 2 || $i < 1) {
            throw new \Exception('--Invalid arguments. Use CLIENT_ID and CLIENT SECRET, or ACCESS_TOKEN');
        }

        if ($i == 1) {
            $this->ll_access_token = func_get_arg(0);
        }

        if ($i == 2) {
            $this->client_id = func_get_arg(0);
            $this->client_secret = func_get_arg(1);
        }*/
    }

    /**
     * @param null $enable
     *
     * @return bool
     */
    public function sandbox_mode($enable = NULL) {
        if (!is_null($enable)) {
            $this->sandbox = $enable === TRUE;
        }

        return $this->sandbox;
    }

    /**
     * Get Access Token for API use
     */
    public function get_access_token() {
        if (isset ($this->ll_access_token) && !is_null($this->ll_access_token)) {
            return $this->ll_access_token;
        }

        $app_client_values = $this->build_query(array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials'
        ));

        $access_data = \ecollect\Core\Lib\RestClient::post("/oauth/token", $app_client_values, "application/x-www-form-urlencoded");

        if ($access_data["status"] != 200) {
            throw new \Exception ($access_data['response']['message'], $access_data['status']);
        }

        $this->access_data = $access_data['response'];

        return $this->access_data['access_token'];
    }

    /**
     * Get information for specific payment
     * @param int $id
     * @return array(json)
     */
    public function get_payment($id) {
        $access_token = $this->get_access_token();

        $uri_prefix = $this->sandbox ? "/sandbox" : "";

        $payment_info = \ecollect\Core\Lib\RestClient::get($uri_prefix."/collections/notifications/" . $id . "?access_token=" . $access_token);

        return $payment_info;
    }

    public function get_paymentE($order_id, $arr) {

        //file_put_contents("log.html","Api get_PaymentE order $order_id<br> arr".json_encode($arr)."<br>",FILE_APPEND);
        //$access_token = $this->get_access_token();

        //$uri_prefix = $this->sandbox ? "/sandbox" : "";

        //$payment_info = \ecollect\Core\Lib\RestClient::get($uri_prefix."/collections/notifications/" . $id . "?access_token=" . $access_token);
        $ticketId = "";
        if (file_exists("ecoi")) {
            //file_put_contents("log.html","before items <br>",FILE_APPEND);
            $itemsTxt = file_get_contents("ecoi");
            //file_put_contents("log.html","items txt $itemsTxt <br>",FILE_APPEND);
            $itemsJson = json_decode($itemsTxt);
            //file_put_contents("log.html","items json ".json_encode($itemsJson)." <br>",FILE_APPEND);
            $items = get_object_vars($itemsJson);
            //file_put_contents("log.html","items array ".json_encode($items)."<br>",FILE_APPEND);
            $ticketId = $items[$order_id];
            //file_put_contents("log.html","ticketId = $ticketId <br>",FILE_APPEND);
        }

        $entityCode = $arr['entityCode'];
        $srvCode = $arr['srvCode'];
        $srvCurrency = $arr['srvCurrency'];
        $wdslProduction = $arr['wdslProduction'];
        $wdslTest = $arr['wdslTest'];
        $testmode = $arr['testmode'];

        $eCollect = new eCollectApi();
        $eCollect->setEntityCode($entityCode);
        $eCollect->setTicketId($ticketId);
        $eCollect->setSrvCode($srvCode);
        $eCollect->setSrvCurrency($srvCurrency);
        $eCollect->setWdslProduction($wdslProduction);
        $eCollect->setWdslTest($wdslTest);
        $eCollect->setSandbox($testmode);

        //file_put_contents("log.html","before getTransactionInformation<br> <br>",FILE_APPEND);
        $arr = array();
        $data_json =  $eCollect->getSessionToken();
        if($data_json->ReturnCode == "SUCCESS"){
            $response = $eCollect->getTransactionInformation($data_json->SessionToken, $order_id);
            //file_put_contents("log.html","before transactionState <br>".json_encode($response),FILE_APPEND);

            //$transactionState = $response->getTransactionInformationResult->tranState;
            $transactionState = $eCollect->getTransactionState();
            //file_put_contents("log.html","transactionState $transactionState <br>",FILE_APPEND);

            //$returnCode = $response->getTransactionInformationResult->ReturnCode;
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
                    default:
                        $returnCode = "cancelled";
                }
            }
            else {
                $returnCode = false;
            }

            //file_put_contents("log.html","return code $returnCode <br>",FILE_APPEND);
            $arr["status"] = $returnCode;
            $arr["status_detail"] = $returnCode;
            $arr["external_reference"] = $order_id;
            $arr["id"] = $ticketId;
            //file_put_contents("log.html","getTransactionInformation <br>".json_encode($response),FILE_APPEND);

            $arr["ecollectTitle"] = "Pagado con eCollect";
            $arr["ecollectTicket"] = $ticketId;
            $arr["ecollectTrazabilityCode"] = $eCollect->getTrazabilityCode();
            $arr["ecollectTransactionState"] = $transactionState;
            $arr["ecollectBankProcessDate"] = $eCollect->getBankProcessDate();
            $arr["ecollectBankName"] = $eCollect->getBankName();
        }else{
            //echo "failed to result:" . $data_json->ReturnCode . "<br>";
            
        }

        return $arr;
    }
    /**
     * @param $id
     *
     * @return array
     */
    public function get_payment_info($id) {
        return $this->get_payment($id);
    }

    /**
     * Get information for specific authorized payment
     * @param id
     * @return array(json)
     */
    public function get_authorized_payment($id) {
        $access_token = $this->get_access_token();

        $authorized_payment_info = \ecollect\Core\Lib\RestClient::get("/authorized_payments/" . $id . "?access_token=" . $access_token);
        return $authorized_payment_info;
    }

    /**
     * Refund accredited payment
     * @param int $id
     * @return array(json)
     */
    public function refund_payment($id) {
        $access_token = $this->get_access_token();

        $refund_status = array(
            "status" => "refunded"
        );

        $response = \ecollect\Core\Lib\RestClient::put("/collections/" . $id . "?access_token=" . $access_token, $refund_status);
        return $response;
    }

    /**
     * Cancel pending payment
     * @param int $id
     * @return array(json)
     */
    public function cancel_payment($id) {
        $access_token = $this->get_access_token();

        $cancel_status = array(
            "status" => "cancelled"
        );

        $response = \ecollect\Core\Lib\RestClient::put("/collections/" . $id . "?access_token=" . $access_token, $cancel_status);
        return $response;
    }

    /**
     * Cancel preapproval payment
     * @param int $id
     * @return array(json)
     */
    public function cancel_preapproval_payment($id) {
        $access_token = $this->get_access_token();

        $cancel_status = array(
            "status" => "cancelled"
        );

        $response = \ecollect\Core\Lib\RestClient::put("/preapproval/" . $id . "?access_token=" . $access_token, $cancel_status);
        return $response;
    }

    /**
     * Search payments according to filters, with pagination
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @return array(json)
     */
    public function search_payment($filters, $offset = 0, $limit = 0) {
        $access_token = $this->get_access_token();

        $filters["offset"] = $offset;
        $filters["limit"] = $limit;

        $filters = $this->build_query($filters);

        $uri_prefix = $this->sandbox ? "/sandbox" : "";

        $collection_result = \ecollect\Core\Lib\RestClient::get($uri_prefix."/collections/search?" . $filters . "&access_token=" . $access_token);
        return $collection_result;
    }

    /**
     * Create a checkout preference
     * @param array $preference
     * @return array(json)
     */
    public function create_preference($preference) {

        //file_put_contents("ecollectApi.html","create preference<br>");

        $entityCode = $preference['entityCode'];
        $srvCode = $preference['srvCode'];
        $srvCurrency = $preference['srvCurrency'];
        $wdslProduction = $preference['wdslProduction'];
        $wdslTest = $preference['wdslTest'];
        $sandbox = $preference['testmode'];
        $amount = $preference['amount'];
        $order_id = $preference['external_reference'];

        $api = new eCollectApi();
        $api->setSandbox($sandbox);
        $api->setWdslProduction($wdslProduction);
        $api->setWdslTest($wdslTest);
        $api->setEntityCode($entityCode);
        $api->setSrvCode($srvCode);
        $api->setSrvCurrency($srvCurrency);
        $api->setWdsl();
        $this->ecollectApi = $api;

        $idContrato = (int)$order_id;//$order->get_transaction_id();

        $idFactura = $order_id;
        $idCliente = $order_id;
        $tipoDocIdentificacion = "CC";
        //var_dump($preference);
        $docIdentificacion = $preference['payer']['dni'];
        //$docIdentificacion = "123456789";
        $firstName = $preference['payer']['first_name'];
        $lastName = $preference['payer']['last_name'];
        $nombreCompleto = "$firstName $lastName";
        //file_put_contents("ecollectApi.html","nombre completo $nombreCompleto<br>",FILE_APPEND);
        $direccion = trim($preference['payer']['address']['street_name']);
        $telefono = "99999999";
        $email = $preference['payer']['email'];
        $redirect = $preference['notification_url']."?topic=payment&id=$order_id";

        $this->ecollectApi->setTransactionValue($amount);
        $this->ecollectApi->setIdCliente($idCliente);
        $this->ecollectApi->setIdContrato($idContrato);
        $this->ecollectApi->setIdFactura($idFactura);
        $this->ecollectApi->setTipoDocIdentificacion($tipoDocIdentificacion);
        $this->ecollectApi->setDocIdentificacion($docIdentificacion);
        $this->ecollectApi->setNombreCompleto($nombreCompleto);
        $this->ecollectApi->setDireccion($direccion);
        $this->ecollectApi->setTelefono($telefono);
        $this->ecollectApi->setEmail($email);
        $this->ecollectApi->setURLRedirect($redirect);

        $data_json =  $this->ecollectApi->getSessionToken();
        

        if($data_json->ReturnCode == "SUCCESS"){
            //file_put_contents("ecollectApi.html","before createTransactionPayment<br>",FILE_APPEND);
            $result = $this->ecollectApi->createTransactionPayment($data_json->SessionToken);
            //file_put_contents("ecollectApi.html","AFTER createTransactionPayment COMENTADO<br>",FILE_APPEND);
            $status = $this->ecollectApi->getReturnCode();
            //file_put_contents("ecollectApi.html","AFTER this->ecollectApi->getReturnCode<br>",FILE_APPEND);
            $ticketId = $this->ecollectApi->getTicketId();
            //file_put_contents("ecollectApi.html","AFTER this->ecollectApi->getTicketId<br>",FILE_APPEND);
            if($status == "SUCCESS"){
                if (file_exists("ecoi")) {
                    $items = get_object_vars(json_decode(file_get_contents("ecoi")));
                }
                $items[$order_id.""] = $ticketId;
                file_put_contents("ecoi",json_encode($items));

                ////file_put_contents("log.html","items saved ".json_encode($items)."<br>",FILE_APPEND);

                //$preference_result['ticketId'] = $ticketId;
                //echo "result:" . $this->ecollectApi->getEcollecUrl() . "<br>";
                $preference_result = array();
                $preference_result['status'] = ($ticketId>0) ? 200 : 500;
                $preference_result['init_point'] = $this->ecollectApi->getEcollecUrl();
                $preference_result['ticketId'] = $ticketId;
                //echo "<br>result2:" . json_encode($result) . "<br>";
                $txt = "result ".json_encode($result)."<br>status $status <br>ticketId $ticketId";
            }else{
                $txt = "error ".json_encode($result)."<br>status $status <br>ticketId $ticketId";
                //echo "<br>error:" . json_encode($result) . "<br>";
            }
        }else{
            //echo "failed to result:" . $data_json->ReturnCode . "<br>";
            $txt = "result Error SessionToken <br>status $status <br>ticketId $ticketId";
        }
        //file_put_contents("payLog.html",$txt,FILE_APPEND);

        //file_put_contents("ecollectApi.html","preference_result<br>".json_encode($preference_result),FILE_APPEND);
        return $preference_result;
    }

    /**
     * Update a checkout preference
     * @param string $id
     * @param array $preference
     * @return array(json)
     */
    public function update_preference($id, $preference) {
        $access_token = $this->get_access_token();

        $preference_result = \ecollect\Core\Lib\RestClient::put("/checkout/preferences/{$id}?access_token=" . $access_token, $preference);
        return $preference_result;
    }

    /**
     * Get a checkout preference
     * @param string $id
     * @return array(json)
     */
    public function get_preference($id) {
        $access_token = $this->get_access_token();

        $preference_result = \ecollect\Core\Lib\RestClient::get("/checkout/preferences/{$id}?access_token=" . $access_token);
        return $preference_result;
    }

    /**
     * Create a preapproval payment
     * @param array $preapproval_payment
     * @return array(json)
     */
    public function create_preapproval_payment($preapproval_payment) {
        $access_token = $this->get_access_token();

        $preapproval_payment_result = \ecollect\Core\Lib\RestClient::post("/preapproval?access_token=" . $access_token, $preapproval_payment);
        return $preapproval_payment_result;
    }

    /**
     * Get a preapproval payment
     * @param string $id
     * @return array(json)
     */
    public function get_preapproval_payment($id) {
        $access_token = $this->get_access_token();

        $preapproval_payment_result = \ecollect\Core\Lib\RestClient::get("/preapproval/{$id}?access_token=" . $access_token);
        return $preapproval_payment_result;
    }

    /**
     * Update a preapproval payment
     * @param string $preapproval_payment, $id
     * @return array(json)
     */

    public function update_preapproval_payment($id, $preapproval_payment) {
        $access_token = $this->get_access_token();

        $preapproval_payment_result = \ecollect\Core\Lib\RestClient::put("/preapproval/" . $id . "?access_token=" . $access_token, $preapproval_payment);
        return $preapproval_payment_result;
    }

    /**
     * Create a custon payment
     * @param array $preference
     * @return array(json)
     */
    public function create_custon_payment($info) {
        $access_token = $this->get_access_token();

        $preference_result = \ecollect\Core\Lib\RestClient::post("/checkout/custom/create_payment?access_token=" . $access_token, $info);
        return $preference_result;
    }

    /* Generic resource call methods */

    /**
     * Generic resource get
     * @param uri
     * @param params
     * @param authenticate = true
     */
    public function get($uri, $params = null, $authenticate = true) {
        $params = is_array ($params) ? $params : array();

        if ($authenticate !== false) {
            $access_token = $this->get_access_token();

            $params["access_token"] = $access_token;
        }

        if (count($params) > 0) {
            $uri .= (strpos($uri, "?") === false) ? "?" : "&";
            $uri .= $this->build_query($params);
        }

        $result = \ecollect\Core\Lib\RestClient::get($uri);
        return $result;
    }

    /**
     * Generic resource post
     * @param uri
     * @param data
     * @param params
     */
    public function post($uri, $data, $params = null) {
        $params = is_array ($params) ? $params : array();

        $access_token = $this->get_access_token();
        $params["access_token"] = $access_token;

        if (count($params) > 0) {
            $uri .= (strpos($uri, "?") === false) ? "?" : "&";
            $uri .= $this->build_query($params);
        }

        $extra_params =  array('platform: ' . $this->_platform, 'so;', 'type: ' .  $this->_type);
        $result = \ecollect\Core\Lib\RestClient::post($uri, $data, "application/json", $extra_params);
        return $result;
    }

    /**
     * Generic resource put
     * @param uri
     * @param data
     * @param params
     */
    public function put($uri, $data, $params = null) {
        $params = is_array ($params) ? $params : array();

        $access_token = $this->get_access_token();
        $params["access_token"] = $access_token;

        if (count($params) > 0) {
            $uri .= (strpos($uri, "?") === false) ? "?" : "&";
            $uri .= $this->build_query($params);
        }

        $result = \ecollect\Core\Lib\RestClient::put($uri, $data);
        return $result;
    }

    /**
     * Generic resource delete
     * @param uri
     * @param data
     * @param params
     */
    public function delete($uri, $params = null) {
        $params = is_array ($params) ? $params : array();

        $access_token = $this->get_access_token();
        $params["access_token"] = $access_token;

        if (count($params) > 0) {
            $uri .= (strpos($uri, "?") === false) ? "?" : "&";
            $uri .= $this->build_query($params);
        }

        $result = \ecollect\Core\Lib\RestClient::delete($uri);
        return $result;
    }

    /* **************************************************************************************** */

    /**
     * @param $params
     *
     * @return string
     */
    private function build_query($params) {
        if (function_exists("http_build_query")) {
            return http_build_query($params, "", "&");
        } else {
            $elements = [];
            foreach ($params as $name => $value) {
                $elements[] = "{$name}=" . urlencode($value);
            }

            return implode("&", $elements);
        }
    }

    /**
     * @param null $platform
     */
    public function set_platform($platform)
    {
        $this->_platform = $platform;
    }

    /**
     * @param null $so
     */
    public function set_so($so = '')
    {
        $this->_so = $so;
    }

    /**
     * @param null $type
     */
    public function set_type($type)
    {
        $this->_type = $type;
    }

}

