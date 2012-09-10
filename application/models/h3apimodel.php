<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
        $this->global_lib->getconfig();
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
    	//if ($this->regtotal() > 30800) exit;
    	
    	// BaaS 사전등록 조회
    	$data['url']="registration?filter=EMAIL='".$this->input->post('email')."'";
    	$data['post']="false";
    	$data['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
   	
    	$result=$this->global_lib->baas_curl($data);
  	
    	if ($result['http_code']!=200) {
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
    	
    	print_r($data);
   	
    	if ($result['http_code']!=200) {
    		$result = $this->db->exec("update reg_data set totalcount=totalcount-1");
    		log_message('Error', '[regpost] BaaS 등록 실패 : '.$result['http_code'].' - '.$result['error_text']);    		
    		$this->global_lib->json_result(array(code=>'-2'));
    	} else {
			$this->global_lib->json_result(array(code=>'0'));
    	}
   }
   
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
	   			$this->global_lib->json_result(array(code=>'0'));
	   		}
	   	}
   }
   
} 