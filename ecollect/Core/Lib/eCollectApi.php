<?php
/**
 * Created by PhpStorm.
 * User: cuent
 * Date: 18/11/2017
 * Time: 16:24
 */

namespace ecollect\Core\Lib;

class eCollectApi
{
    private $wdsl;
    private $EntityCode;
    private $ApiKey;
    private $SrvCode;
    private $TransValue;
    private $TransVatValue;
    private $SrvCurrency;
    private $URLRedirect;
    private $ReferenceArray;
    private $idCliente;
    private $idContrato;
    private $idFactura;
    private $tipoDocIdentificacion;
    private $docIdentificacion;
    private $nombreCompleto;
    private $direccion;
    private $telefono;
    private $email;
    private $returnCode;
    private $eCollectUrl;
    private $ticketId;
    private $response;
    private $trazabilityCode;
    private $transactionState;
    private $payCurrency;
    private $currencyRate;
    private $bankProcessDate;
    private $bankName;
    private $paymentSystem;
    private $sandbox;
    private $wdslText;
    private $wdslProduction;

    function __construct() {
        //file_put_contents("log.html","eCollectApi construct <br>",FILE_APPEND);

        //$this->wdsl = "https://test1.e-collect.com/d_Express/webservice/eCollectWebservicesv2.asmx?wsdl";
        $this->sandbox = true;
        $this->wdslTest = "https://test1.e-collect.com/app_express/api/";
        $this->wdslProduction = "https://gateway1.ecollect.co/app_express/";
        $this->wdsl = "https://test1.e-collect.com/app_express/api/";
        $this->EntityCode = "50024";
        $this->ApiKey = "456A327663334F356555486C70377135433731672F664A597A4F32504A576A79";
        $this->SrvCode = "1";
        $this->SrvCurrency = "COP";
        $this->TransVatValue = 0;
        $this->URLRedirect = "http://www.prosadata.com/ecollect.php"; //checkout
        $referenceArray = array();
        for ($i = 0; $i < 8; $i++) {
            $referenceArray[] = "";
        }
        $this->ReferenceArray = $referenceArray;
        $this->log();
    }

    function getSid() {        
        //return $this->_session->getSessionId();
        return session_id();
    }

    function getSessionToken() {
        $this->setWdsl();
        $url = $this->wdsl."getSessionToken";

        $data = array(
            "EntityCode" => $this->EntityCode,
            "ApiKey" => $this->ApiKey
        );
        $data_string =  json_encode($data); 

        $ch = curl_init();
        if ($ch === false) {
            echo "failed to initialize module curl";            
            die;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //$result = curl_exec($ch);
        if(!$result = curl_exec($ch)) 
        { 
            echo "failed to result<br>";
            //die;
        }
        curl_close($ch);
        return json_decode($result);
    }

    function createTransactionPayment($SessionToken) {
        $this->setWdsl();
        $this->log();

        //$client = new \Zend\Soap\Client($this->wdsl,null);

        $referenceArray = array();
        $referenceArray[] = $this->tipoDocIdentificacion;
        $referenceArray[] = $this->nombreCompleto;
        $referenceArray[] = $this->docIdentificacion;        
        $referenceArray[] = $this->email;        
        $referenceArray[] = $this->idFactura;
        //$referenceArray[] = $this->idCliente;
        //$referenceArray[] = $this->telefono;
        //Opcionales
        //$referenceArray[] = $this->direccion;        
        //$referenceArray[] = $this->idContrato;

        $ipPublica = $_SERVER['REMOTE_ADDR']; 
        $paymentInfoArray = array(
            array(
                "AttributeCode" => 26,
                "AttributeDesc" => "MerchantTransactionId",
                "AttributeValue" => $this->EntityCode."". $this->idFactura,
            ),
            array(
                "AttributeCode" => 6,
                "AttributeDesc" => "Usermail",
                "AttributeValue" => $this->email,
            ),
            array(
                "AttributeCode" => 19,
                "AttributeDesc" => "CardHolderId",
                "AttributeValue" => $this->docIdentificacion,
            ),
            array(
                "AttributeCode" => 23,
                "AttributeDesc" => "IPAddress",
                "AttributeValue" => $ipPublica,
            ),
            array(
                "AttributeCode" => 24,
                "AttributeDesc" => "DeviceFingerPrint",
                "AttributeValue" => $this->getSid(),
            )
        );

        $request = array(
            "EntityCode" => $this->EntityCode,
            "SessionToken" => $SessionToken,
            "SrvCode" => $this->SrvCode,
            "TransValue" => $this->TransValue,
            "TransVatValue" => $this->TransVatValue,
            "SrvCurrency" => $this->SrvCurrency,
            "URLRedirect" => $this->URLRedirect,
            "LangCode" => "ES",
            "Invoice" => "",
            "ReferenceArray" => $referenceArray,
            "PaymentInfoArray" => $paymentInfoArray
        );

        /* Set your parameters for the request */
        /*$params = array(
            "request" => $request
        );*/
        
        $url = $this->wdsl."createTransactionPayment";        
        $data_string =  json_encode($request); 
        //echo $url;
        //echo $data_string;

        /* Invoke webservice method with your parameters, in this case: Function1 */
        //$response = $client->__call("createTransactionPayment", array($params));
        $ch = curl_init();
        if ($ch === false) {
            echo "failed to initialize module curl";            
            die;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(!$result = curl_exec($ch)) 
        { 
            echo "Failed to result<br>";
        }
        /*$info = curl_getinfo($ch);
        var_dump($info);*/
        curl_close($ch);

        $response = json_decode($result);        
        //var_dump($response);

        //$createTransactionPaymentResult = $response->createTransactionPaymentResult;
        $this->returnCode = $response->ReturnCode;
        $this->ticketId = $response->TicketId;
        $this->eCollectUrl = $response->eCollectUrl;
        $this->response = $response;
        //file_put_contents("ecollectApi.html","response<br>".json_encode($response),FILE_APPEND);

        $this->log();
        return $response;
    }

    function getTransactionInformation($SessionToken, $order_id) {
        $this->setWdsl();
        //$client = new \Zend\Soap\Client($this->wdsl,null);

        $paymentInfoArray = array(
            array(
                "AttributeCode" => 26,
                "AttributeDesc" => "MerchantTransactionId",
                "AttributeValue" => $this->EntityCode."". $order_id,
            )
        );

        $request = array(
            "EntityCode" => $this->EntityCode,
            "SessionToken" => $SessionToken,
            "TicketId" => $this->ticketId,
            "PaymentInfoArray" => $paymentInfoArray
        );        

        /*$params = array(
            "request" => $request
        );*/
        //file_put_contents("log.html","before call params ".json_encode($params)."<br>",FILE_APPEND);
        
        $url = $this->wdsl."getTransactionInformation";        
        $data_string =  json_encode($request); 
         
        //var_dump($data_string);  
        $ch = curl_init();

        //$response = $client->__call("getTransactionInformation", array($params));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(!$result = curl_exec($ch)) 
        { 
            echo "Failed to result<br>";
        }
        //$info = curl_getinfo($ch);
        //var_dump($info);
        curl_close($ch);

        $response = json_decode($result);  
        //var_dump($response);
        //die;

        $this->response = $response;

        //file_put_contents("log.html","response ".json_encode($response)."<br>",FILE_APPEND);

        $getTransactionInformationResult = $response;
        $this->EntityCode = $getTransactionInformationResult->EntityCode;
        $this->ticketId = $getTransactionInformationResult->TicketId;
        $this->trazabilityCode = $getTransactionInformationResult->TrazabilityCode;
        $this->transactionState = $getTransactionInformationResult->TranState;
        $this->returnCode = $getTransactionInformationResult->ReturnCode;
        $this->TransValue = $getTransactionInformationResult->TransValue;
        $this->TransVatValue = $getTransactionInformationResult->TransVatValue;
        $this->payCurrency = $getTransactionInformationResult->PayCurrency;
        $this->currencyRate = $getTransactionInformationResult->CurrencyRate;
        $this->bankProcessDate = $getTransactionInformationResult->BankProcessDate;
        if(isset($getTransactionInformationResult->BankName)){
            $this->bankName = $getTransactionInformationResult->BankName; 
        } else if(isset($getTransactionInformationResult->FiName)){
            $this->bankName = $getTransactionInformationResult->FiName; 
        } else{
            $this->bankName = "Pagado con eCollect";
        }          
        $this->paymentSystem = $getTransactionInformationResult->PaymentSystem;
        if ($this->returnCode == "SUCCESS") {
             $referenceArray = $getTransactionInformationResult->ReferenceArray;
            //var_dump($referenceArray);
            /*$this->idCliente = $referenceArray[0];
            $this->idContrato = $referenceArray[1];
            $this->tipoDocIdentificacion = $referenceArray[3];
            $this->nombreCompleto = $referenceArray[4];
            $this->direccion = $referenceArray[5];
            $this->telefono = $referenceArray[6];
            $this->email = $referenceArray[7];*/
            $this->tipoDocIdentificacion = $referenceArray[0];
            $this->nombreCompleto = $referenceArray[1];
            $this->docIdentificacion = $referenceArray[2];
            $this->email = $referenceArray[3];
            $this->idFactura = $referenceArray[4];
        }

        //file_put_contents("log.html","before return response getTransactionInformation<br>",FILE_APPEND);
        return $response;
    }

    function setWdsl() {
        $this->wdsl = ($this->sandbox) ? $this->wdslTest: $this->wdslProduction;
    }

    function getWdsl() {
        return $this->wdsl;
    }

    function setWdslProduction($wdslProduction) {
        //file_put_contents("logE.txt","$wdslProduction");
        $this->wdslProduction = $wdslProduction;
    }

    function getWdslProduction() {
        return $this->wdslProduction;
    }

    function setWdslTest($wdslTest) {
        //file_put_contents("logWdslTest.txt","$wdslTest");
        $this->wdslTest = $wdslTest;
    }

    function getWdslTest() {
        return $this->wdslText;
    }

    function setSandbox($sandbox) {
        $sandbox = ("1" == $sandbox);
        $this->sandbox = $sandbox;
    }

    function getSandbox() {
        return $this->sandbox;
    }

    function setEntityCode($entityCode) {
        $this->EntityCode = $entityCode;
    }

    function setApiKey($apiKey) {
        $this->ApiKey = $apiKey;
    }

    function setSrvCode($srvCode) {
        $this->SrvCode = $srvCode;
    }

    function setTransactionValue($transValue) {
        $this->TransValue = "".$transValue;
    }

    function setTransactionVatValue($transVatValue) {
        $this->TransVatValue = $transVatValue;
    }

    function setSrvCurrency($srvCurrency) {
        $this->SrvCurrency = $srvCurrency;
    }

    function setURLRedirect($urlRedirect) {
        $this->URLRedirect = $urlRedirect;
    }

    function setReferenceArray($referenceArray) {
        $this->ReferenceArray = $referenceArray;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }

    function setIdContrato($idContrato) {
        $this->idContrato = $idContrato;
    }

    function setIdFactura($idFactura) {
        $this->idFactura = $idFactura;
    }

    function setTipoDocIdentificacion($tipoDocIdentificacion) {
        $this->tipoDocIdentificacion = $tipoDocIdentificacion;
    }

    function setDocIdentificacion($docIdentificacion) {
        $this->docIdentificacion = $docIdentificacion;
    }

    function setNombreCompleto($nombreCompleto) {
        $this->nombreCompleto = $nombreCompleto;
    }

    function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setTicketId($ticketId) {
        $this->ticketId = $ticketId;
    }

    function getTransactionValue() {
        return $this->TransValue;
    }

    function getTransactionVatValue() {
        return $this->TransVatValue;
    }

    function getReturnCode() {
        return $this->returnCode;
    }

    function getTicketId() {
        return $this->ticketId;
    }

    function getEntityCode() {
        return $this->EntityCode;
    }

    function getApiKey() {
        return $this->ApiKey;
    }

    function getIdCliente() {
        return $this->idCliente;
    }

    function getIdContrato() {
        return $this->idContrato;
    }

    function getIdFactura() {
        return $this->idFactura;
    }

    function getTipoDocIdentificacion() {
        return $this->tipoDocIdentificacion;
    }

    function getDocIdentificacion() {
        return $this->docIdentificacion;
    }

    function getNombreCompleto() {
        return $this->nombreCompleto;
    }

    function getDireccion() {
        return $this->direccion;
    }

    function getTelefono() {
        return $this->telefono;
    }

    function getEmail() {
        return $this->email;
    }

    function getEcollecUrl() {
        return $this->eCollectUrl;
    }

    function getResponse() {
        return $this->response;
    }

    function getTrazabilityCode() {
        return $this->trazabilityCode;
    }

    function getTransactionState() {
        return $this->transactionState;
    }

    function getPayCurrency(){
        return $this->payCurrency;
    }

    function getCurrencyRate() {
        return $this->currencyRate;
    }

    function getBankProcessDate() {
        return $this->bankProcessDate;
    }

    function getBankName() {
        return $this->bankName;
    }

    function getPaymentSystem() {
        return $this->paymentSystem;
    }

    function log() {
        $txt = "";
        $txt .= "<br>wdsl = ".$this->wdsl;
        $txt .= "<br>EntityCode = ".$this->EntityCode;
        $txt .= "<br>ApiKey = ".$this->ApiKey;
        $txt .= "<br>SrvCode = ".$this->SrvCode;
        $txt .= "<br>TransValue = ".$this->TransValue;
        $txt .= "<br>TransVatValue = ".$this->TransVatValue;
        $txt .= "<br>SrvCurrency = ".$this->SrvCurrency;
        $txt .= "<br>URLRedirect = ".$this->URLRedirect;
        $txt .= "<br>ReferenceArray = ".json_encode($this->ReferenceArray);
        $txt .= "<br>idCliente = ".$this->idCliente;
        $txt .= "<br>idContrato = ".$this->idContrato;
        $txt .= "<br>idFactura = ".$this->idFactura;
        $txt .= "<br>tipoDocIdentificacion = ".$this->tipoDocIdentificacion;
        $txt .= "<br>docIdentificacion = ".$this->docIdentificacion;
        $txt .= "<br>nombreCompleto = ".$this->nombreCompleto;
        $txt .= "<br>direccion = ".$this->direccion;
        $txt .= "<br>telefono = ".$this->telefono;
        $txt .= "<br>email = ".$this->email;
        $txt .= "<br>returnCode = ".$this->returnCode;
        $txt .= "<br>eCollectUrl = ".$this->eCollectUrl;
        $txt .= "<br>ticketId = ".$this->ticketId;
        $txt .= "<br>response = ".json_encode($this->response);
        $txt .= "<br>trazabilityCode = ".$this->trazabilityCode;
        $txt .= "<br>transactionState = ".$this->transactionState;
        $txt .= "<br>payCurrency = ".$this->payCurrency;
        $txt .= "<br>currencyRate = ".$this->currencyRate;
        $txt .= "<br>bankProcessDate = ".$this->bankProcessDate;
        $txt .= "<br>bankName = ".$this->bankName;
        $txt .= "<br>paymentSystem = ".$this->paymentSystem;
        $txt .= "<br>sandbox = ".$this->sandbox;
        $txt .= "<br>wdslTest = ".$this->wdslTest;
        $txt .= "<br>wdslProduction = ".$this->wdslProduction;
        //file_put_contents("logEcollect.html",$txt);
    }


}

?>