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
      
        $arrayField = array(
        "applicationId" => "APP_014086",
        "password" => "34a957801d34126bb54c592bab1a9dcf",
        "message" => "Hello Friend",
        "destinationAddresses" => ["tel:B%3C4syfNGoCtonwa/ENJ961lg1cmq6pWz0m+5mBnTliLT3aiDqPYAc9dpKD+QLV6GRnnHSc35zTH6h36G2aED48O0w=="]
        
        );
 

       $jsonObjectFields = json_encode($arrayField);
        
       print_r($jsonObjectFields);
       die();
    
        //return $this->curlPOSTsms($jsonObjectFields);
        
    }
    //$app_id, $password, $message, $dest_addr
    public function getSendMessageJson( ) {
        return "{\n\t\"applicationId\": \"APP_014086\",\n\t\"password\": \"34a957801d34126bb54c592bab1a9dcf\",\n\t\"message\": \"hello there\",\n\t\"destinationAddresses\": [\"tel:AZ110uk76PIgB9RwcuA9JuF4N\\/SkIDEI2OIAKfBBRy8H6\\/W4Hi66VUqwA2zcEQe5VtB\\/YfQhPyp7XBVWmru2cwT1tow==\"]\n\t}";
    
    }

    public function curlPOSTsms($jsonObjectFields){
        $url = "";

        $url = "https://developer.bdapps.com/sms/send";
       
        $method = "POST";
        $header = [
            "Content-type: application/json",
            "Accept : application/json"
        ];
        $post_fields = $jsonObjectFields;

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
