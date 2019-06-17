<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Components\Curl;
use App\Http\Components\SmsSender;
use App\SmsSaved;
use App\Content;
use App\SubscriptionData;

class SMSController extends Controller
{

    public function smsSend(Request $request){
        $url = "https://developer.bdapps.com/sms/send";
        //$app_id = "APP_014086";
        $app_id = $request->input('app_id');
        $message = $request->input('message');
      // $password = "34a957801d34126bb54c592bab1a9dcf";
        $password = $request->input('password');

        
        $sms_ob = new SmsSender($url, $app_id, $password);
        
        $response =  $sms_ob->broadcast($message);
        
        return $response;
    }


    public function cronSmsSend(){

        $url = "https://developer.bdapps.com/sms/send";
        $app_id = "APP_014086"; 
        $obj = Content::orderBy('created_at', 'DESC')->where('is_sent', false)->get()->first();
        $message = isset($obj->content) ? $obj->content : "N/A" ;
        $password = "34a957801d34126bb54c592bab1a9dcf";
        $sms_ob = new SmsSender($url, $app_id, $password);
        
        $response =   json_decode($sms_ob->broadcast($message));
        $statusCode = $response->statusCode;
        dd($statusCode);
        
        if($statusCode == 'S1000'){
            $obj->is_sent = true;
            if($obj->save()){
                return $response;
            }else{
                return "Data saving error";
             }
        }
        return $res;
    }
    
//     public function smsSend(Request $request){
    
//         $app_id = $request->input('app_id');
//         $password = $request->input('password');
//        
//         $dest_addr = $request->input('dest_addr');
       
// //        $message_json =  $this->getSendMessageJson($app_id, $password, $message, $dest_addr);
      
//         $arrayField = array(
//         "applicationId"=>"APP_014086",
//         "password"=>"34a957801d34126bb54c592bab1a9dcf",
//         "message"=>"Hello Friend",
//         "destinationAddresses"=>["tel:B%3C4syfNGoCtonwa/ENJ961lg1cmq6pWz0m+5mBnTliLT3aiDqPYAc9dpKD+QLV6GRnnHSc35zTH6h36G2aED48O0w=="]

//         );
 

//        $jsonObjectFields = json_encode($arrayField);
        
    
//         return $this->curlPOSTsms($jsonObjectFields);
        
//     }
//     //$app_id, $password, $message, $dest_addr
    public function getSendMessageJson( ) {
        return "{\n\t\"applicationId\": \"APP_014086\",\n\t\"password\": \"34a957801d34126bb54c592bab1a9dcf\",\n\t\"message\": \"hello there\",\n\t\"destinationAddresses\": [\"tel:AZ110uk76PIgB9RwcuA9JuF4N\\/SkIDEI2OIAKfBBRy8H6\\/W4Hi66VUqwA2zcEQe5VtB\\/YfQhPyp7XBVWmru2cwT1tow==\"]\n\t}";
    
    }

    public function curlPOSTsms($jsonObjectFields){
        
        $url = "https://developer.bdapps.com/sms/send";
       
        $method = "POST";
        $header = [
            "Content-type: application/json",
            "Accept : application/json"
        ];
        $post_fields = $jsonObjectFields;

        return Curl::call($url, $method, $header, $post_fields);
    }
       /** This is for generating random 6 digit string for doctor's referral. 
     * 
     */
    public function generateRandomString($length, $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz') {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[rand(0, $max)];
        }
        return strtoupper($str);
    }

    public function sendSubsriptionSmsToSubscriber($app_id, $message, $subscriberId){
        $url = "https://developer.bdapps.com/sms/send";

        $password = "34a957801d34126bb54c592bab1a9dcf";
        $sms_ob = new SmsSender($url, $app_id, $password);
        $res =   $sms_ob->sms($message, $subscriberId);

    }

    public function smsRecieve(Request $request){
        $data = [
            'success' => false,
            'message' => 'error occured' 
  
        ];

        // if(!empty($inspect)){
        //     $arr1 = explode(' ',trim($inspect));
        //     $str = $arr1[1];

        //     $message =  $request->input('message');	
        //     $phone =  $request->input('phone');	
        //     $requestId =  $request->input('requestId');		
        //     $encoding =  $request->input('encoding');	
        //     $applicationId =  $request->input('applicationId');		
        //     $sourceAddress =  $request->input('sourceAddress');	
        //     $version =  $request->input('version');		

        //     $sms_req = new Sms();
        //     $sms_req->message = isset($message) ?  $message : "";
        //     $sms_req->requestId = isset($requestId) ?  $requestId : "";
        //     $sms_req->encoding = isset($encoding) ?  $encoding : "";
        //     $sms_req->applicationId = isset($applicationId) ?  $applicationId : "";
        //     $sms_req->sourceAddress = isset($sourceAddress) ?  $sourceAddress : "";
        //     $sms_req->version = isset($version) ?  $version : "";
        
        // }else{
            

        // }

        $version =  $request->input('version');	
        $applicationId =  $request->input('applicationId');		
        $subscriberId =  $request->input('subscriberId');	
        $status =  $request->input('status');		
        $frequency =  $request->input('frequency');	
        $timeStamp =  $request->input('timeStamp');			
      
        if($status == "REGISTERED"){
            $subData = new SubscriptionData();
            $subData->subscriberId =  $subscriberId;
            $otp 	= $this->generateRandomString(6);
            $subData->otp = $otp;

            if($subData->save()){
                $msg = "You have successfully subscribed to our service. Your code is:" . $otp ." Please use this Code to avail your service. Thank you ";
                $musk = "tel:".$subscriberId;
                $this->sendSubsriptionSmsToSubscriber($applicationId, $msg, $musk);
            }
            
        }


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

    public function checkSubscriptionCodeOfSubscriber(Request $request){

        $otp = $request->input('code');

        if(!empty($otp)){
            $check = SubscriptionData::where('otp' , $otp)->get()->first();
            
            if(empty($check)){
                $data['is_there'] = false;
            }else{
                $data['is_there'] = true;
            }

        }else{
            $data['message'] = "Code is not found in api parameter";
        }

        return response()->json($data);


    }
     

    


}
