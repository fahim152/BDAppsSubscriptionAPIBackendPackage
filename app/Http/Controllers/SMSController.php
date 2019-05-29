<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Components\Curl;

class SMSController extends Controller
{
    
    public function smsSend(Request $request){
     
        $app_id = $request->input('app_id');
        $password = $request->input('password');
        $message = $request->input('message');
        $dest_addr = $request->input('dest_addr');

        $message_json =  $this->getSendMessageJson($app_id, $password, $message, $dest_addr);
        $sendsmsrequest = $this->curlPOSTsms($message_json);

      
        return response()->json($sendsmsrequest);
        

    }
    public function getSendMessageJson($app_id, $password, $message, $dest_addr ) {
        return "{  { \"applicationId\":\"$app_id\",\"password\":\"$password\", \"message\":\"$message\", \"destinationAddresses\":\"$dest_addr\" } }";
    }

    public function curlPOSTsms($message_json){
        $url = "";

        $url = "https://developer.bdapps.com/sms/send";
       
        $method = "POST";
        $header = [
            "content-type: application/json",
            "accept: application/json",
        ];
        $post_fields = $message_json ;

        return Curl::call($url, $method, $header, $post_fields);
    }


}
