<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Components\Curl;
use App\Http\Components\SmsSender;

use App\SmsSaved;
use App\Content;
use App\AppPass;
use App\MessageData;
use App\SubscriptionData;

class SMSController extends Controller
{

    public function smsSend(Request $request){
        $url = "https://developer.bdapps.com/sms/send";
  
        $message = $request->input('message');
        $app_id = $request->input('app_id');
        $password = $request->input('password');

        $sms_ob = new SmsSender($url, $app_id, $password);  
        $response =  $sms_ob->broadcast($message);
        
        return $response;
    }


    public function cronSmsSend(Request $request){
        
        $salt = "DF7AFB9CBA953DA385CA76882FEB3";
        
        if($request->input('salt') == $salt ){
        $url = "https://developer.bdapps.com/sms/send";
        $app_id = "APP_014086"; 
        $obj = Content::orderBy('created_at', 'DESC')->where('is_sent', false)->get()->first();
        $message = isset($obj->content) ? $obj->content : "N/A" ;
        $password = "34a957801d34126bb54c592bab1a9dcf";
        $sms_ob = new SmsSender($url, $app_id, $password);
        
        if(!empty($obj)){
            $response =   $sms_ob->broadcast($message);
            $res_obj = json_decode($response);
       
            if($res_obj->statusCode == 'S1000'){
                $obj->is_sent = true;
                if($obj->save()){

                    $data['message'] = "SMS sent to all subscriber ! and db updated successfully ";
                    $data['response'] = $response;
                    return $data;
                }else{
                    $data['message']= "SMS sent to all subscriber ! but Database update error !! ";
                    $data['response'] = $response;
                    return $data;
                 }
            }else{
                $data['message']= "SMS not sent check server response statusCode & statusDetails for more" ;
                $data['response'] = $response;
                return $data;
            }
            
        }else{
            $response['message']= "Database is empty or no more unsent message available ! please insert content";
            return $response;
        }
    }
    $data['alert'] = "ALERT !!!!!! OPERATION ABORTED ! ENCRYPTION KEY DOESN'T MATCH. THIS INCIDENT WILL BE RECORDED ALONG WITH IP_ADDR";
    return $data;
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
        $password = AppPass::where('AppId', $app_id)->pluck('password')->first();
     
        $sms_ob = new SmsSender($url, $app_id, $password);
        $res =   $sms_ob->sms($message, $subscriberId);

    }

    public function smsRecieve(Request $request){
   
        // if request has requestId parameter that means user sending sms with some text. 
        if(isset($request->requestId)){
            $message =  $request->input('message');	
            $requestId =  $request->input('requestId');		
            $encoding =  $request->input('encoding');	
            $applicationId =  $request->input('applicationId');		
            $sourceAddress =  $request->input('sourceAddress');	
            $version =  $request->input('version');	

            $messageData = new MessageData();
            $messageData->message = $message;
            $messageData->requestId = $requestId;
            $messageData->encoding = $encoding;
            $messageData->applicationId = $applicationId;
            $messageData->sourceAddress = $sourceAddress;
            $messageData->version = $version;
            $otp 	= $this->generateRandomString(6);
            $messageData->otp = $otp;

            if($messageData->save()){
                $msg = "You have successfully subscribed to our service. Your code is:" . $otp ." Please use this Code to avail your service. Thank you ";
                $this->sendSubsriptionSmsToSubscriber($applicationId, $msg, $sourceAddress);
                $data['sucess'] = true;
                $data['message'] = "Data Saved";

            }else{
                $data['sucess'] = false;
                $data['message'] = "Messaeg Data saving error.";
            }


        }else{

        $version =  $request->input('version');	
        $applicationId =  $request->input('applicationId');		
        $subscriberId =  $request->input('subscriberId');	
        $status =  $request->input('status');		
        $frequency =  $request->input('frequency');	
        $timeStamp =  $request->input('timeStamp');			
      
        if($status == "REGISTERED"){
            $subData = new SubscriptionData();
            $subData->appId =  $applicationId;
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
    }
        return $data;
        
        
    }

    public function checkSubscriptionCodeOfSubscriber(Request $request){

        $otp = $request->input('code');
        $device_id = $request->input('device_id');

        if(!empty($otp)){
            $check = SubscriptionData::where('otp' , $otp)->get()->first();
            if(!empty($device_id)){
                $device_check = SubscriptionData::where('device_id' , $device_id)->pluck('device_id')->first();
              
                if($device_check !== $device_id){
                     $data['message'] = "This OTP is already used in other device.";
                     return response()->json($data);
                }
            }
            if(empty($check)){
                $data['is_there'] = false;
            }else{
                $check->device_id = isset($device_id) ? $device_id : null;
                $check->save();
                $data['is_there'] = true;
            }

        }else{
            $data['message'] = "Code is not found in api parameter";
        }
        return response()->json($data);


    }

    public function addSubscriberPass(Request $request){
    if($request->pwd == "bdapps2019"){
       $app_id = isset($request->app_id) ? $request->app_id : null;
       $password =  isset($request->password) ? $request->password : null;

       if( $app_id !== null && $password !== null ){
           $is_exist = AppPass::where ("AppId", $app_id)->get()->first();
           if(empty($is_exist)){
            $app = new AppPass;
            $app->AppId = $app_id;
            $app->password = $password;
            $app->save();

            $data = [
                'app_id' => $app_id,
                'password' => $password,
            ];
          
           }else{
            return "this APP ID already exist on DB";
           }

       }else{
           return "you must specify app_id and password both";
       }
       return $data;
    }
    else{
        return "you are not authenticated";
    }
}


    // public function checkMessageDataOtp(Request $request){
    //     $otp = $request->input('code');
    //     $device_id = $request->input('device_id');

    //     if(!empty($otp)){
    //         $check = MessageData::where('otp' , $otp)->get()->first();
    //         if(!empty($device_id && $device_id !== null )){
    //             $device_check = MessageData::where('device_id' , $device_id)->pluck('device_id')->first();
             
    //             $allMessageOfThisDeviceId = MessageData::select('message')->where('device_id', $device_id)->get();
    //             foreach($allMessageOfThisDeviceId as $l){
    //                 array_push($arr, $l);
    //             } 
    //                 $msgdata = isset($arr) ? $arr : "";
    //             if($device_check == null){
                    
    //                 $check->device_id = isset($device_id) ? $device_id : null;
    //                 $check->save();
                   
    //             }
    //             if($device_check !== $device_id){
                        
    //                 $data['message'] = "This OTP is already used in other device.";
    //                 return response()->json($data);
    //             }
    //         }else{
    //             $data['message'] = "You have not sent device ID.";
    //                 return response()->json($data);
    //         }
    //         if(empty($check)){
    //             $data['is_there'] = false;
    //         }else{
    //             $check->device_id = isset($device_id) ? $device_id : null;
    //             $check->save();
    //             $data['is_there'] = true;
    //             $data['returned_message'] = isset($msgdata) ? $msgdata : "" ;
  
    //         }

    //     }else{
    //         $data['message'] = "Code is not found in api parameter";
    //     }
    //     return response()->json($data);




    // }


    
    public function checkMessageDataOtp(Request $request){
        $otp = $request->input('code');
        $device_id = $request->input('device_id');

        if(isset($otp)){
            if(isset($device_id)){
                $check = MessageData::where('otp' , $otp)->get()->first();
            
                if(isset($check)){
                    $device_check = MessageData::where('otp' , $otp)->pluck('device_id')->first();
                    if($device_check == null || empty($device_check)){
                        $check->device_id = $device_id;
                        $check->save();
                        $data['success'] = true;
                        $data['message'] = $check->message;
                    }else{
                        if($device_id == $device_check){
                            $arr = array();
                            $allMessageOfThisDeviceId = MessageData::select('message')->where('device_id', $device_id)->pluck('message');
                           
                                 foreach($allMessageOfThisDeviceId as $list){
                                         array_push($arr, $list);
                                      }
                                    $msgdata = isset($arr) ? $arr : "";
                                    
                            $data['message'] = $msgdata;
                            $data['success'] = true;
                        }else{
                              $data['success'] = false;
                            $data['message'] = "Your device id not matched";
                        }
                    }
                }else{
                    $data['success'] = false;
                    $data['message'] = "OTP Not found";
                }
            }else{
                $data['message'] = "No Device ID";
            }
        }else{
            $data['message'] = "Code is not found in api parameter";
        }
        return response()->json($data);
    }
       
}
