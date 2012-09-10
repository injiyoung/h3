<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 @author : hdae124@kthcorp.com
 @date : 2012. 9. 10.
*/

class H3apimodel extends CI_Model {
	var $db ="";
	
    function __construct()
    {
    	parent::__construct();
    	
    	$this->load->library('global_lib');
    	$this->global_lib->bass_token();
    	    	
        try{
        	$this->db = new PDO ("sqlite:h3.sqlite");
        } catch(PDOException $e){
        	$errorinfo=$this->db->errorInfo();
        	log_message('Error', '[sqlite] DB연동 실패'.$e);
        	$this->global_lib->json_result(array(code=>'-1'));
        }
    }
    
    function getconfig()
    {
    	return $this->global_lib->getconfig();
    }
    
    function retoken()
    {
    	$this->global_lib->bass_token(array('reload'=>'Y'));
    }
    
   function regtotal() 
   {
    	$stmt = $this->db->prepare('SELECT * FROM reg_data');
    	if (!$stmt) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regtotal] table 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array(code=>'-1'));
    	}
    	$result=$stmt->execute();
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regtotal] select 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array(code=>'-1'));
    	}
    	$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);
     	return $row[0];
    }
    
    function regpost()
    {
    	// 시작/마감 시간, 사전등록제한카운트 가져오기
    	$stmt = $this->db->prepare('SELECT * FROM reg_date');
    	if (!$stmt) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regpost] table 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array(code=>'-1'));
    	}
    	$result=$stmt->execute();
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regpost] select 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array(code=>'-1'));
    	}
    	$row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
    	
    	// 사전등록 제한 카운트 체크
    	if ($this->regtotal() >= $row['max_count'])
    	{
    		$this->global_lib->json_result(array(code=>'-12'));
    	}
    	// 시작시간 체크
    	if ($row['start_date'] >= date( "Y-m-d H:i:s", strtotime('now')))
    	{
    		$this->global_lib->json_result(array(code=>'-10'));
    	}
    	// 마감시간 체크
    	if ($row['end_date'] <= date( "Y-m-d H:i:s", strtotime('now')))
    	{
    		$this->global_lib->json_result(array(code=>'-11'));
    	}    	
    	    	
    	// BaaS 사전등록 조회
    	$data['url']="registration?filter=EMAIL='".$this->input->post('email')."'";
    	$data['post']="false";
    	$data['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
   	
    	$result=$this->global_lib->baas_curl($data);
  	
    	
    	if ($result['http_code']!=200) {
    		$this->output->set_status_header('500');
    		log_message('Error', '[regpost] BaaS 조회 실패 : '.$result['http_code'].' - '.$result['error_text']);
    		$this->global_lib->json_result(array(code=>'-2'));
    	} else {
    		$json_result=json_decode($result[result_data],true);
    		if ($json_result['entities']) {
    			// 이미 등록된 이메일
    			$this->global_lib->json_result(array(code=>'-13'));    			
    		}    		    	
    	}

    	// DB조회
    	$result = $this->db->exec("update reg_data set totalcount=totalcount+1");
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regpost] insert 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array(code=>'-1'));
    	}
    	
    	$data['url']="registration";
    	$data['post']="true";
    	$data['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
    	$data['postfields']=json_encode(array("MEMBER_UUID"=>$this->input->post('uuid'),"NAME"=>$this->input->post('name'),"EMAIL"=>$this->input->post('email'),"COMPANY"=>$this->input->post('company') ));
    	
    	$result=$this->global_lib->baas_curl($data);
    	
    	if ($result['http_code']!=200) {
    		$this->output->set_status_header('500');
    		$result = $this->db->exec("update reg_data set totalcount=totalcount-1");
    		log_message('Error', '[regpost] BaaS 등록 실패 : '.$result['http_code'].' - '.$result['error_text']);    		
    		$this->global_lib->json_result(array(code=>'-2'));
    	} else {
			$this->global_lib->json_result(array(code=>'0'));
    	}
   }
   
   // 메일 발송
   function schpwd()
   {
	   	// BaaS조회
	   	$data['url']="registration?filter=EMAIL='".$this->input->get('email')."'";
	   	$data['post']="false";
	   	$data['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
	   	
	   	$result=$this->global_lib->baas_curl($data);
	   	 
	   	if ($result['http_code']!=200) {
	   		log_message('Error', '[schpwd] BaaS 조회 실패 : '.$result['http_code'].' - '.$result['error_text']);
	   		$this->global_lib->json_result(array(code=>'-2'));
	   	} else {
	   		$json_result=json_decode($result[result_data],true);
	   		if (!$json_result['entities']) {
	   			// 등록되지 않은 이메일
	   			$this->global_lib->json_result(array(code=>'-11'));	   			
	   		} else {
	   			// 메일 발송
	   			$data['subject']="메롱";
	   			$data['body']="바바";
	   			$data['email']=$this->input->get('email');
	   			$this->global_lib->send_mail($data);
	   			
	   			$this->global_lib->json_result(array(code=>'0'));
	   		}
	   	}
   }
   
} 