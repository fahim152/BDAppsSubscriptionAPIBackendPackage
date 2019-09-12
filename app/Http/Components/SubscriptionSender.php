<?php

namespace App\Http\Components;

class Subscription{
    var $server;
	var $password;
	var $applicationId;

    public function __construct($server,$password,$applicationId){
        $this->server = $server; // Assign server url
		$this->password = $password;
		$this->applicationId = $applicationId;
    }
	
	 public function getStatus($address){
		 
		 $this->server = 'https://developer.bdapps.com/subscription/getstatus';

        $arrayField = array("applicationId" => $this->applicationId,
            "password" => $this->password,
            "subscriberId" => $address);

        $jsonObjectFields = json_encode($arrayField);
        $x =  $this->sendRequest($jsonObjectFields);
        return $x->subscriptionStatus;
    }

    public function subscribe($address){
		
		$this->server = 'https://developer.bdapps.com/subscription/send';

        $arrayField = array("applicationId" => $this->applicationId,
            "password" => $this->password,
            "subscriberId" => $address,
            "version" => "1.0",
			"action" => "1");

        $jsonObjectFields = json_encode($arrayField);
        return $this->sendRequest($jsonObjectFields);
    }
	public function unSubscribe($address){
		$this->server = 'https://developer.bdapps.com/subscription/send';
        $arrayField = array("applicationId" => $this->applicationId,
            "password" => $this->password,
            "subscriberId" => $address,
            "version" => "1.0",
			"action" => "0");

        $jsonObjectFields = json_encode($arrayField);
        return $this->sendRequest($jsonObjectFields);
    }

    private function sendRequest($jsonObjectFields){
        $ch = curl_init($this->server);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonObjectFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $this->handleResponse($res);
    }

    private function handleResponse($resp){
        if ($resp == "") {
            throw new SubscriptionException("Server URL is invalid", '500');
        } else {
            return json_decode($resp);
        }
    }

}