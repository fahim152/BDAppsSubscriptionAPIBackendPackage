<?php

namespace App\Http\Components;

class SmsSender{
	private $applicationId,
			$password,
			$charging_amount,
			$encoding,
			$version,
			$deliveryStatusRequest,
			$binaryHeader,
			$sourceAddress,
			$serverURL;
	
	/* Send the server name, app password and app id
	*	Dialog Production Severurl : HTTPS : - https://api.dialog.lk/sms/send
	*				     HTTP  : - http://api.dialog.lk:8080/sms/send
	*/		
	public function __construct($serverURL, $applicationId, $password)
	{
		if(!(isset($serverURL, $applicationId, $password)))
			throw new SMSServiceException('Request Invalid.', 'E1312');
		else {
			$this->applicationId = $applicationId;
			$this->password = $password;
			$this->serverURL = $serverURL;
		}
    }
    
    public function sendRequest($jsonStream,$url){

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStream);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
		return $res;

	}
	
	// Broadcast a message to all the subcribed users
	public function broadcast($message){
		return $this->sms($message, array('tel:all'));
	}
	
	// Send a message to the user with a address or send the array of addresses
	public function sms($message, $addresses){
		if(empty($addresses))
			throw new SMSServiceException('Format of the address is invalid.', 'E1325');
		else {
			$jsonStream = (is_string($addresses))?$this->resolveJsonStream($message, array($addresses)):(is_array($addresses)?$this->resolveJsonStream($message, $addresses):null);
			return ($jsonStream!=null)?$this->handleResponse( $this->sendRequest($jsonStream,$this->serverURL) ):false;
		
		}
	}
	
	private function handleResponse($jsonResponse){
	
		$statusCode = $jsonResponse->statusCode;
		$statusDetail = $jsonResponse->statusDetail;
		
		if(empty($jsonResponse))
			throw new SMSServiceException('Invalid server URL', '500');
		else if(strcmp($statusCode, 'S1000')==0)
			return "true";
		else
			throw new SMSServiceException($statusDetail, $statusCode);
	}
	
	private function resolveJsonStream($message, $addresses){
		
		$messageDetails = array("message"=>$message,
	   	           				"destinationAddresses"=>$addresses
           					);
		
		if (isset($this->sourceAddress)) {
			$messageDetails= array_merge($messageDetails,array("sourceAddress" => $this->sourceAddress));   
		}
		
		if (isset($this->deliveryStatusRequest)) {
			$messageDetails= array_merge($messageDetails,array("deliveryStatusRequest" => $this->deliveryStatusRequest));
		}
		
		if (isset($this->binaryHeader)) {
			$messageDetails= array_merge($messageDetails,array("binaryHeader" => $this->binaryHeader));
		}	
		
		if (isset($this->version)) {
			$messageDetails= array_merge($messageDetails,array("version" => $this->version)); 
		}	
		
		if (isset($this->encoding)) {
			$messageDetails= array_merge($messageDetails,array("encoding" => $this->encoding)); 
		}
		
		$applicationDetails = array('applicationId'=>$this->applicationId,
						 'password'=>$this->password,);
		
		$jsonStream = json_encode($applicationDetails+$messageDetails);
		
		return $jsonStream;
	}

	public function setsourceAddress($sourceAddress){
		$this->sourceAddress=$sourceAddress;
	}

	public function setcharging_amount($charging_amount){
		$this->charging_amount=$charging_amount;
	}

	public function setencoding($encoding){
		$this->encoding=$encoding;
	}

	public function setversion($version){
		$this->version=$version;
	}

	public function setbinaryHeader($binaryHeader){
		$this->binaryHeader=$binaryHeader;
	}

	public function setdeliveryStatusRequest($deliveryStatusRequest){
		$this->deliveryStatusRequest=$deliveryStatusRequest;
	}
}


class SMSServiceException extends Exception{
	private $statusCode,
	$statusDetail;

	public function __construct($message, $code){
		parent::__construct($message);

		$this->statusCode = $code;
		$this->statusDetail = $message;
	}

	public function getErrorCode(){
		return $this->statusCode;
	}

	public function getErrorMessage(){
		return $this->statusDetail;
	}
}

