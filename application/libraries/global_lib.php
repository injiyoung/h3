<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Global_lib {
	public function __construct()
	{
		try{
			$this->db = new PDO ("sqlite:h3.sqlite");
		} catch(PDOException $e){
			$errorinfo=$this->db->errorInfo();
			log_message('Error', 'DB연동 실패'.$e);
			exit;
		}		
	}
	
    public function baas_curl($data)
    {
    	$CI =& get_instance();
    	$ch = curl_init();
	   	curl_setopt($ch, CURLOPT_URL, $CI->config->item('ext_bass_url').$data['url']);
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
    
    // 토큰 만료 확인및 재발급
    public function bass_token() 
    {
    	$CI =& get_instance();
    	$stmt = $this->db->prepare('SELECT * FROM token');
    	$result=$stmt->execute();
    	$errorinfo=$this->db->errorInfo();    	
    	
    	$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);
	
    	if ($row[0]<=strtotime('now') or !$row[1]) 
    	{
    		$data['url']="token?grant_type=client_credentials&client_id=".$CI->config->item('ext_bass_client_id')."&client_secret=".$CI->config->item('ext_bass_client_secret');
    		$data['post']="false";
    		$data['httpheader']=array("Authorization: Bearer YWMtEY0l1PfLEeGj_QIAU6kAEwAAATmekgi_F2Tw2X260g0DgRusiKQeuzNLje8");
    		$data['postfields']="";
    		$result=$this->baas_curl($data);
    		$result_json=json_decode($result['result_data'],true);
    		
    		$token=$result_json['access_token'];
    		
    		$stmt = $this->db->prepare("update token set expire_time='".strtotime('now +'.$result_json['expires_in'].' sec ')."',token='".$result_json['access_token']."' ");
    		$result=$stmt->execute();
    	} else {
    		$token=$row[1];
    	}
    	return $token;
    }
    
    
 }
 