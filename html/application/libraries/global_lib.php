<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Global_lib {

	
	public function __construct()
	{
	
		// Do something with $params
	}
	
     public function baas_curl($data)
     {
     	$ch = curl_init();
     	curl_setopt($ch, CURLOPT_URL, $data['url']);
     	curl_setopt($ch, CURLOPT_POST, $data['post']);
     	curl_setopt($ch, CURLOPT_HTTPHEADER, $data['httpheader']);
     	curl_setopt($ch, CURLOPT_POSTFIELDS, $data['postfields']);
     	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
     	$result_data=curl_exec($ch);  
     	
     	if (curl_error($ch)) {
     		$info['error_text']=curl_error($ch);
     	} else {
     		$info = curl_getinfo($ch);
     		$info['result_data']=$result_data;     		
     	}
     	
     	return $info;
     }
 }
