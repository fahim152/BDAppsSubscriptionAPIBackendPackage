<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Components\Curl;
use App\SmsSaved;

class SMSController extends Controller
{
    
    public function smsSend(Request $request){
     
        $app_id = $request->input('app_id');
        $password = $request->input('password');
        $message = $request->input('message');
        $dest_addr = $request->input('dest_addr');

//        $message_json =  $this->getSendMessageJson($app_id, $password, $message, $dest_addr);
      
        $arrayField = array("applicationId" => $app_id,
        "password" => $password,
        "message" => $message,
        "destinationAddresses" => $dest_addr,
        "deliveryStatusRequest" => "1",
        "chargingAmount" => '0.10',

     
        );

       $jsonObjectFields = json_encode($arrayField);
      
      
        // $sendsmsrequest = $this->curlPOSTsms($message_json);

      
        return $this->curlPOSTsms($jsonObjectFields);
        
    }
    public function getSendMessageJson($app_id, $password, $message, $dest_addr ) {
        return "{  { \"applicationId\":\"$app_id\",\"password\":\"$password\", \"message\":\"$message\", \"destinationAddresses\":\"$dest_addr\" } }";
    }

    public function curlPOSTsms($jsonObjectFields){
        $url = "";

        $url = "https://developer.bdapps.com/sms/send";
       
        $method = "POST";
        $header = [
            "content-type: application/json",
            "accept: application/json",
        ];
        $post_fields = $jsonObjectFields ;

        return Curl::call($url, $method, $header, $post_fields);
    }

    public function smsRecieve(Request $request){

           
        $data = [
            'success' => false,
            'message' => 'error occured' 
  
        ];
        
        $version =  $request->input('version');	
        $applicationId =  $request->input('applicationId');		
        $subscriberId =  $request->input('subscriberId');	
        $status =  $request->input('status');		
        $frequency =  $request->input('frequency');	
        $timeStamp =  $request->input('timeStamp');			

        $sms = new SmsSaved();

        $sms->version = isset($version) ? $version : "";
        $sms->applicationId = isset($applicationId) ? $applicationId : "";		
        $sms->subscriberId = isset($subscriberId) ? $subscriberId : "";	
        $sms->status = isset($status) ? $status : "";		
        $sms->frequency = isset($frequency) ? $frequency : "";
        $sms->timeStamp = isset($timeStamp) ? $timeStamp : "";  

        if($sms->save()){
            $data['sucess'] = true;
            $data['message'] = "Data Saved";
        }

        return $data;
        
        

    }
     



    


}
