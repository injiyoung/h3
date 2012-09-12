<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/*
 @author : hdae124@kthcorp.com
 @date : 2012. 9. 11.
*/

class Global_lib {
	var $app_token="";
	
	function __construct()
	{
		try{
			$this->db = new PDO ("sqlite:h3.sqlite");
		} catch(PDOException $e){
			$errorinfo=$this->db->errorInfo();
			log_message('Error', 'DB연동 실패'.$e);
			$this->global_lib->json_result(array(code=>'-1'));
		}		
	}
	
 	/**
	 * 2012. 9. 12. hdae124@kthcorp.com
	 * 이메일 발송
	 */ 
	function send_mail($data)
	{
		//$data['subject']="메롱";
		//$data['body']="바바";
		//$data['email']="hdae124@paran.com";
		//$this->global_lib->send_mail($data);
		
		$CI =& get_instance();
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://mail.als.kthcorp.com:8082/1/email/outbound/request");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode("puddingto:".$CI->config->item('ext_email'))));
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('from'=>'H3 <h3@kthcorp.com>','to'=>$data['email'],'Subject'=>$data['subject'],'Contents'=>$data['body']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$result_data=curl_exec ($ch);
		curl_close($ch);
		
		return $result_data;
	}
		
    /**
     * 2012. 9. 12. hdae124@kthcorp.com
     * baas연결용 curl
     */ 
    function baas_curl($data)
    {
    	$CI =& get_instance();
    	$ch = curl_init();
	   	curl_setopt($ch, CURLOPT_URL, $CI->config->item('ext_bass_url').$data['url']);
    	curl_setopt($ch, CURLOPT_POST, $data['post']);
    	if (@$data['httpheader']) curl_setopt($ch, CURLOPT_HTTPHEADER, @$data['httpheader']);
	   	if (@$data['postfields']) curl_setopt($ch, CURLOPT_POSTFIELDS, @$data['postfields']);
	   	if (@$data['customerquest']) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, @$data['customerquest']);
     	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
     	$result_data=curl_exec($ch);

     	if (curl_error($ch)) {
     		$info['error_text']=curl_error($ch);
     	} else {
     		$info = curl_getinfo($ch);
     		$info['result_data']=$result_data;     		
     	}
     	curl_close($ch);
     	
     	return $info;
    }
    
    /**
     * 2012. 9. 12. hdae124@kthcorp.com
     * 토큰 만료 확인및 재발급
     */ 
    function bass_token($data="") 
    {
    	$CI =& get_instance();
    	$stmt = $this->db->prepare('SELECT * FROM token');
    	$result=$stmt->execute();
    	$errorinfo=$this->db->errorInfo();    	
    	
    	$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);
	
    	if ($row[0]<=strtotime('now') or !$row[1] or @$data['reload']=='Y') 
    	{
    		$data['url']="token?grant_type=client_credentials&client_id=".$CI->config->item('ext_bass_client_id')."&client_secret=".$CI->config->item('ext_bass_client_secret');
    		$data['post']="false";
    		$result=$this->baas_curl($data);
    		$result_json=json_decode($result['result_data'],true);
    		$token=$result_json['access_token'];
    		
    		$stmt = $this->db->prepare("update token set expire_time='".strtotime('now +'.$result_json['expires_in'].' sec ')."',token='".$result_json['access_token']."' ");
    		$result=$stmt->execute();
    	} else {
    		$token=$row[1];
    	}
    	
    	$this->apptoken=$token;
    }

    /**
     * 2012. 9. 12. hdae124@kthcorp.com
     * BaaS 환경설정 가져오기
     */ 
    function getConfig()
    {
    	if (!$this->apptoken) $this->bass_token();
    	$data['url']="h3info";
    	$data['post']="false";
    	$data['httpheader']=array("Authorization: Bearer ".$this->apptoken);
    	$result=$this->baas_curl($data);
    	$result_json=json_decode($result['result_data'],true);
  	
    	return $result_json['entities'][0];
    }
    
    /**
     * 2012. 9. 12. hdae124@kthcorp.com
     * 최종 json encode
     */ 
    function json_result($data)
    {
    	echo json_encode($data);
    	exit;
    }
    
    
 }
 