<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 공통 라이브러리
 *
 * Created on 2012. 9. 10.
 * @author miyu <hdae124@kthcorp.com>
 */

class Global_lib {
	var $apptoken="";
	var $CI="";
	
	function __construct()
	{
		try{
			$this->db = new PDO ("sqlite:h3.sqlite");
		} catch(PDOException $e){
			$errorinfo=$this->db->errorInfo();
			log_message('Error', 'DB연동 실패'.$e);
			$this->global_lib->json_result(array(code=>'-1'));
		}
		$this->CI =& get_instance();
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
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://mail.als.kthcorp.com:8082/1/email/outbound/request");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode("puddingto:".$this->CI->config->item('ext_email'))));
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
    	if (!$this->apptoken and @$data['mode']!='token') $this->bass_token();
    	
    	$ch = curl_init();
	   	curl_setopt($ch, CURLOPT_URL, $this->CI->config->item('ext_bass_url').$data['url']);
    	curl_setopt($ch, CURLOPT_POST, $data['post']);
    	if ($this->apptoken) curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$this->apptoken));
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

     		if ($info['http_code']!="200") {
     			log_message('Error', '['.$data['url'].'] BaaS 조회 실패 : '.@$result['http_code'].' - '.@$result['error_text']);
     			$this->error_result(array('code'=>'-2','code_text'=>'BaaS오류'),$info['http_code']);
     		}
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
    	$stmt = $this->db->prepare('SELECT * FROM token');
    	$result=$stmt->execute();
    	$errorinfo=$this->db->errorInfo();    	
    	
    	$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);
	
    	if ($row[0]<=strtotime('now') or !$row[1] or @$data['reload']=='Y') 
    	{
    		$cdata['url']="token?grant_type=client_credentials&client_id=".$this->CI->config->item('ext_bass_client_id')."&client_secret=".$this->CI->config->item('ext_bass_client_secret');
    		$cdata['post']="false";
    		$cdata['mode']="token";
    		$result=$this->baas_curl($cdata);
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
    	// --------------------------------------------------------------------------
    	// 가장 최근에 수정된 h3info를 가지고 온다.
    	$cdata['url']="h3info?ql=".urlencode('select * order by modified desc')."&limit=1";
    	$cdata['post']="false";
    	$result=$this->baas_curl($cdata);
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
    
    function error_result($data,$errorcode='500') {
    	log_message('Error',@$data['code'].' : '.@$data['code_text']);
    	//$this->CI->output->set_status_header($errorcode);    	
    	$this->json_result($data);
    }
 }

 
/* End of file global_lib.php */
/* Location: /2012/application/libraries/global_lib.php */