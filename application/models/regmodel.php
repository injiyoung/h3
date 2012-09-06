<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Regmodel extends CI_Model {
	var $db ="";

    function __construct()
    {
    	parent::__construct();
        try{
        	$this->db = new PDO ("sqlite:h3.sqlite");
        } catch(PDOException $e){
        	$errorinfo=$this->db->errorInfo();
        	log_message('Error', 'DB연동 실패'.$e);
        	exit;
        }
        
        $this->load->library('global_lib');        
    }

   function totalcount() 
    {
    	$stmt = $this->db->prepare('SELECT * FROM reg_data');
    	if (!$stmt) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', 'table 실패 : '.$errorinfo[2]);
    		exit;
    	}
    	$result=$stmt->execute();
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', 'select 실패 : '.$errorinfo[2]);
    		exit;
    	}
    	$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);
     	return $row[0];
    }
    
    function insertreg()
    {
    	if ($this->totalcount() > 30800) exit;    	
    	
    	$result = $this->db->exec("update reg_data set totalcount=totalcount+1");
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', 'insert 실패 : '.$errorinfo[2]);
    		exit;
    	}
    	
    	$data['url']="http://stageapi.baas.io/test-organization/h3site/test15";
    	$data['post']="true";
    	$data['httpheader']=array("Authorization: Bearer YWMtEY0l1PfLEeGj_QIAU6kAEwAAATmekgi_F2Tw2X260g0DgRusiKQeuzNLje8");
    	$data['postfields']=json_encode(array("member_uuid"=>$this->input->post('uuid'),"name"=>$this->input->post('name'),"email"=>$this->input->post('email'),"company"=>$this->input->post('company') ));
    	
    	$result=$this->global_lib->baas_curl($data);
   	
    	if ($result['http_code']!=200) {
    		log_message('Error', 'BaaS 등록 실패 : '.$result['http_code'].' - '.$result['error_text']);
    		$result = $this->db->exec("update reg_data set totalcount=totalcount-1");
    		exit;
    	} else {
			echo $result['result_data'];
    	}
   }
   
} 