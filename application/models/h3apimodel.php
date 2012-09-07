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
        	log_message('Error', 'DB연동 실패'.$e);
        	$this->global_lib->json_result(array(code=>'-1'));
        }        
        $this->global_lib->getconfig();
    }
    
    function getconfig()
    {fgdfgfdfg
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
    		log_message('Error', 'table 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array(code=>'-1'));
    	}
    	$result=$stmt->execute();
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', 'select 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array(code=>'-1'));
    	}
    	$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);
     	return $row[0];
    }
    
    function regpost()
    {
    	//if ($this->regtotal() > 30800) exit;
   	
    	$result = $this->db->exec("update reg_data set totalcount=totalcount+1");
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', 'insert 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array(code=>'-1'));
    	}
    	
    	$data['url']="registration";
    	$data['post']="true";
    	$data['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
    	$data['postfields']=json_encode(array("MEMBER_UUID"=>$this->input->post('uuid'),"NAME"=>$this->input->post('name'),"EMAIL"=>$this->input->post('email'),"COMPANY"=>$this->input->post('company') ));
    	
    	$result=$this->global_lib->baas_curl($data);
   	
    	if ($result['http_code']!=200) {
    		$result = $this->db->exec("update reg_data set totalcount=totalcount-1");
    		log_message('Error', 'BaaS 등록 실패 : '.$result['http_code'].' - '.$result['error_text']);    		
    		$this->global_lib->json_result(array(code=>'-2'));
    	} else {
			$this->global_lib->json_result(array(code=>'0'));
    	}
   }
   
} 